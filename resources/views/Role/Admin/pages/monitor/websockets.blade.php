<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>WebSocket Live Monitor</title>
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:"Segoe UI",sans-serif;background:#f9fafb;padding:24px;color:#111827}
    h1{font-size:2rem;margin-bottom:24px;font-weight:600}
    .grid{display:grid;grid-template-columns:repeat(auto‑fit,minmax(340px,1fr));gap:24px}
    .card{background:#fff;border-radius:14px;padding:20px;box-shadow:0 4px 14px rgba(0,0,0,.06)}
    .card h2{font-size:1.2rem;margin-bottom:14px;font-weight:500;display:flex;align-items:center;gap:6px}
    .event{margin-bottom:18px}
    .event-title{font-weight:600}
    .avatars{display:flex;flex-wrap:wrap;gap:10px;margin-top:10px}
    .avatar{display:flex;align-items:center;gap:6px;background:#f3f4f6;border-radius:40px;padding:4px 10px}
    .avatar img{width:26px;height:26px;border-radius:50%;object-fit:cover}
    .small{font-size:.85rem;color:#6b7280}
    .tag{background:#e5e7eb;color:#111827;font-size:.75rem;border-radius:4px;padding:2px 6px;margin-left:6px}
    .tag.internal{background:#fef3c7;color:#92400e}
    .subs{margin-top:6px;font-size:.85rem;color:#1f2937}
    .scroll{max-height:360px;overflow:auto}
    ::-webkit-scrollbar{height:6px;width:6px}
    ::-webkit-scrollbar-thumb{background:#d1d5db;border-radius:4px}
  </style>
</head>
<body>
  <h1>📡 Websocket Live Monitor</h1>

  <div class="grid">
    <!-- Live events -->
    <div class="card">
      <h2>🗓️ Live Events</h2>
      <div id="events" class="scroll small">Waiting for data…</div>
    </div>

    <!-- Raw client list -->
    <div class="card">
      <h2>👥 Connections</h2>
      <div id="clients" class="scroll small">Waiting for data…</div>
    </div>
  </div>

  <script>
    function formatReadableDate(dateString) {
      const date = new Date(dateString);
      if (isNaN(date)) return "";
      const day = date.getDate();
      const month = date.toLocaleString("default", { month: "long" });
      const year = date.getFullYear();
      const ordinal = (n) => {
        const s = ["th", "st", "nd", "rd"];
        const v = n % 100;
        return n + (s[(v - 20) % 10] || s[v] || s[0]);
      };
      return `${ordinal(day)} of ${month}, ${year}`;
    }

    const $events = document.getElementById("events");
    const $clients = document.getElementById("clients");

    async function getWebSocketToken() {
      const res = await fetch("/websocket-token", {
        credentials: "include",
      });
      if (!res.ok) throw new Error("Token fetch failed");
      const data = await res.json();
      return data.token;
    }

    function handleMonitorMessage(data) {
      try {
        const { type, eventScopedConnections, eventDetails, userDetails, clients } = JSON.parse(data);
        if (type !== "monitor_update") return;

        // Events UI
        $events.innerHTML = "";
        if (Object.keys(eventScopedConnections).length === 0) {
          $events.innerHTML = "<em>No active subscriptions.</em>";
        } else {
          for (const [eventId, userIds] of Object.entries(eventScopedConnections)) {
            const evt = eventDetails?.[eventId] ?? {};
            const title = evt.title ? `#${eventId} - ${evt.title}` : `Event ${eventId}`;
            const date = evt.date ? ` · <span class="small">${formatReadableDate(evt.date)}</span>` : "";
            const wrap = document.createElement("div");
            wrap.className = "event";
            wrap.innerHTML = `
              <div class="event-title">${title}${date}</div>
              <div class="avatars">
                ${userIds.map(uid => {
                  const user = userDetails?.[uid];
                  const u = {
                    first_name: user?.first_name ?? "User",
                    last_name: user?.last_name ?? uid,
                    profilePicture: user?.profile_picture?.startsWith("profile_pictures/")
                      ? `storage/${user.profile_picture}`
                      : "/storage/default_profile_image.webp",
                  };
                  return `
                    <div class="avatar">
                      <img src="${u.profilePicture}" alt="">
                      <span>${u.first_name} ${u.last_name} (${uid})</span>
                    </div>
                  `;
                }).join("")}
              </div>
            `;
            $events.appendChild(wrap);
          }
        }

        // Clients UI
        const filteredClients = clients.filter(cl => cl.connectedEvent?.length > 0);
        $clients.innerHTML = "";
        if (!filteredClients.length) {
          $clients.innerHTML = "<em>No connected sockets.</em>";
        } else {
          filteredClients.forEach((cl, i) => {
            const div = document.createElement("div");
            div.style.marginBottom = "14px";
            const subs = cl.connectedEvent.map(s => `#${s.userId}@${s.eventId}`).join(", ");
            div.innerHTML = `
              <strong>Socket ${i + 1}</strong>
              ${cl.isInternal ? '<span class="tag internal">internal</span>' : ''}
              <div class="subs">${subs}</div>
            `;
            $clients.appendChild(div);
          });
        }

      } catch (err) {
        console.error("Parse error", err);
      }
    }

    (async () => {
      try {
        const token = await getWebSocketToken();
        const socket = new WebSocket(`wss://localhost:8080/monitor`, [token]);

        socket.onopen = () => console.log("✅ Monitor connected");
        socket.onerror = e => console.error("❌ Monitor error", e);
        socket.onclose = () => console.warn("⚠️ Monitor closed");
        socket.onmessage = ({ data }) => handleMonitorMessage(data);
      } catch (err) {
        console.error("WebSocket init failed", err);
      }
    })();

  </script>
</body>
</html>