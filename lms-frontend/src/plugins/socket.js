import io from 'socket.io-client';
import Echo from 'laravel-echo';

let api = process.env.VUE_APP_API.replace('/v1', '')
let host = process.env.VUE_APP_ENV === 'PROD' ? `${api}:6002` : 'http://localhost:6002'

window.io = io;

const echo = new Echo({
    broadcaster: 'socket.io',
    transports: ["websocket", "polling"],
    host: host
});

echo.connector.socket.on('connect', () => {
    console.log('Connected to Socket.IO server.');
});

echo.connector.socket.on('connect_error', (error) => {
    console.error('Connection Error:', error);
});

echo.connector.socket.on('disconnect', (reason) => {
    console.warn('Disconnected from server:', reason);
});

echo.connector.socket.on('error', (error) => {
    console.error('Socket Error:', error);
});

export default echo;
