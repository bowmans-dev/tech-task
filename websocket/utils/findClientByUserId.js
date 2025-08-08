export function findClientByUserId(wss, userId) {
  return [...wss.clients].find(c => String(c.userId) === userId && c.readyState === 1);
}