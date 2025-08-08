import jwt from "jsonwebtoken";
import { WebSocket } from "ws";
import net from "net";
import dotenv from "dotenv";
dotenv.config();

const jwtSecret = process.env.JWT_SECRET;

let server;
let wss;

function is_port_in_use(port, callback) {

	const test_server = net.createServer();

	test_server.once("error", (err) => {
		if (err.code === "EADDRINUSE") {
			callback(true);
		} else {
			callback(false);
		}
	});
	test_server.once("listening", () => {
		test_server.close(() => callback(false));
	});
	test_server.listen(port);
}

function generateTestToken(userId = 999) {
  const payload = {
    iss: "jest-test-client",
    iat: Math.floor(Date.now() / 1000),
    exp: Math.floor(Date.now() / 1000) + 60 * 5, // 5 minutes expiry
    isInternal: false,
    sub: userId,
  };

  return jwt.sign(payload, jwtSecret);
}


beforeAll(async () => {
    
    console.warn = () => {};
    console.log = () => {};

	await new Promise((resolve, reject) => {
		is_port_in_use(8080, async (portUsed) => {

			if (portUsed) {
				console.error("Port 8080 is already in use. Please shut down any external server before running tests.");
				return reject(new Error("Port 8080 is in use."));
			}

			try {
				const module = await import("../websocket-server.js");
				server = module.server;
				wss = module.wss;
			} catch (err) {
				return reject(err);
			}

			if (!server.listening) {
				server
				.listen(8080, () => {
					resolve();
				})
				.on("error", (err) => {
					if (err.code === "EADDRINUSE") {
						console.error("Websocket server is already running, cannot start a new instance.");
						reject(err);
					} else {
						console.error("Unexpected websocket server error:", err);
						reject(err);
					}
				});
			} else {
				resolve();
			}
		});
	});
});

afterAll(() => {

	if (wss) {
		wss.clients.forEach((client) => {

			client.removeAllListeners();
			
			if (client.readyState === WebSocket.OPEN) {
				client.terminate();
			}
		});
		wss.close();
	}

	if (server) {
		server.close();
		server.unref();
	}

	setTimeout(() => {}, 500);
});

afterEach(() => {
    if (wss) {
        wss.clients.forEach((client) => {
            client.removeAllListeners();

            if (client.readyState === WebSocket.OPEN) {
                client.terminate();
            }
        });
    }
});

test("only subscribed users receive messages for their event", (done) => {
    const tokenA = generateTestToken(999);
    const tokenB = generateTestToken(777);

    const clientA = new WebSocket("wss://localhost:8080", [tokenA], {
        rejectUnauthorized: false,
    });

    const clientB = new WebSocket("wss://localhost:8080", [tokenB], {
        rejectUnauthorized: false,
    });

    setTimeout(() => {
        clientA.send(JSON.stringify({ action: "message_broadcast", event_id: 100, message: "Hello Event 100", user: { id: 999 } }));
    }, 500);

    clientA.on("message", (data) => {
        const response = JSON.parse(data.toString());
        expect(response.message).toBe("Hello Event 100"); // Should receive this
        clientA.close();
    });

    clientB.on("message", (data) => {
        const response = JSON.parse(data.toString());
        expect(response.message).toBeUndefined(); // Should NOT receive this message
        clientB.close();
    });

    setTimeout(() => done(), 2000); 
}, 30000);


test("users subscribed to different events do not receive messages", (done) => {

    const tokenA = generateTestToken(999);
    const tokenB = generateTestToken(888);

    const clientSubscribedTo100 = new WebSocket("wss://localhost:8080", [tokenA], {
        rejectUnauthorized: false,
    });

    const clientSubscribedTo200 = new WebSocket("wss://localhost:8080", [tokenB], {
        rejectUnauthorized: false,
    });

    clientSubscribedTo100.on("open", () => {
        clientSubscribedTo100.send(JSON.stringify({ action: "connect_to_event", userId: "999", event_id: 100 }));
    });

    clientSubscribedTo200.on("open", () => {
        clientSubscribedTo200.send(JSON.stringify({ action: "connect_to_event", userId: "888", event_id: 200 }));
    });

    setTimeout(() => {
        clientSubscribedTo100.send(JSON.stringify({ action: "message_broadcast", event_id: 100, message: "Event 100 Update", user: { id: 999 } }));
    }, 500);

    clientSubscribedTo100.on("message", (data) => {
        const response = JSON.parse(data.toString());
        expect(response.message).toBe("Event 100 Update");
        clientSubscribedTo100.close();
    });

    clientSubscribedTo200.on("message", (data) => {
        // This client should NOT receive a message for event 100
        done(new Error("Client subscribed to event 200 incorrectly received a message."));
    });

    setTimeout(() => {
        clientSubscribedTo200.close();
        done();
    }, 2000);
}, 30000);

test("user can unsubscribe from an event", (done) => {

    const tokenA = generateTestToken(999);

    const clientA = new WebSocket("wss://localhost:8080", [tokenA], {
        rejectUnauthorized: false,
    });

    clientA.on("open", () => {
        // First, subscribe clientA to an event (Event 100)
        clientA.send(JSON.stringify({ action: "connect_to_event", userId: "999", event_id: 100 }));

        // After subscribing, send unsubscribe request
        setTimeout(() => {
            clientA.send(JSON.stringify({ action: "disconnect_from_event", userId: 999, event_id: 100 }));
        }, 500);
    });

    clientA.on("message", (data) => {
        const response = JSON.parse(data.toString()); 

        // Ensure the unsubscribe response contains correct data
        if (response.action === "disconnect_from_event") {
            expect(response.userId).toBe("999");
            expect(response.event_id).toBe("100");

            // Verify the connection is terminated
            setTimeout(() => {
                expect(clientA.readyState).toBe(WebSocket.CLOSED);
                done();
            }, 500);
        }
    });
}, 30000);


test("broadcasting to different events works independently", (done) => {
    const tokenA = generateTestToken(999);
    const tokenB = generateTestToken(888);

    const clientA = new WebSocket("wss://localhost:8080", [tokenA], {
        rejectUnauthorized: false,
    });

    const clientB = new WebSocket("wss://localhost:8080", [tokenB], {
        rejectUnauthorized: false,
    });

    clientA.on("open", () => {
        clientA.send(JSON.stringify({ action: "connect_to_event", userId: "999", event_id: 100 }));
    });

    clientB.on("open", () => {
        clientB.send(JSON.stringify({ action: "connect_to_event", userId: "888", event_id: 200 }));
    });

    // Give some time for the clients to subscribe
    setTimeout(() => {
        clientA.send(JSON.stringify({ action: "message_broadcast", event_id: 100, message: "Event 100 Update", user: { id: 999 } }));
        clientB.send(JSON.stringify({ action: "message_broadcast", event_id: 200, message: "Event 200 Update", user: { id: 888 } }));
    }, 500);

    let aReceived = false;
    let bReceived = false;

    clientA.on("message", (data) => {
        const response = JSON.parse(data.toString());
        if (response.message === "Event 100 Update") {
            aReceived = true;
        }
    });

    clientB.on("message", (data) => {
        const response = JSON.parse(data.toString());
        if (response.message === "Event 200 Update") {
            bReceived = true;
        }
    });

    // Ensure both messages are received before finishing
    setTimeout(() => {
        if (aReceived && bReceived) {
            done();
        } else {
            done(new Error("Timeout: Not all broadcasts were received."));
        }
    }, 5000); // Increase timeout here to 5000ms
}, 10000); // Update the test timeout to a higher value
