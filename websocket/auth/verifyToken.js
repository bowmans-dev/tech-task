import jwt from "jsonwebtoken";
import dotenv from 'dotenv';
dotenv.config();

export function verifyToken(token) {
    
    if (!token) {
        return { error: "Token missing", code: 4001 };
    }

    try {
        const payload = jwt.verify(token, process.env.JWT_SECRET);
        return { payload };
    } catch (err) {
        return { error: "Invalid token", code: 4002 };
    }
}
