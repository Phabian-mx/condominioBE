import express from 'express';
import http from 'http';
import { Server } from 'socket.io';
import cors from 'cors';

const app = express();

// Configuración para que React (Frontend) pueda conectarse
app.use(cors({
  origin: "http://localhost:5173", // Puerto estándar de Vite
  methods: ["GET", "POST"]
}));

const server = http.createServer(app);

// Configuración de WebSockets
const io = new Server(server, {
  cors: {
    origin: "http://localhost:5173",
    methods: ["GET", "POST"]
  }
});

// --- DATOS DE LA ENCUESTA (En Memoria) ---
let encuesta = {
  pregunta: "¿Se aprueba la instalación de cámaras de seguridad?",
  opciones: {
    '1': { texto: "Sí, es necesario", votos: 0 },
    '2': { texto: "No, es muy caro", votos: 0 }
  },
  usuariosVotaron: [] // Lista de IDs para evitar doble voto
};

// --- LÓGICA DE CONEXIÓN ---
io.on('connection', (socket) => {
  console.log('Cliente conectado:', socket.id);

  // 1. Enviar estado actual al conectarse
  socket.emit('actualizar_encuesta', encuesta);

  // 2. Escuchar votos
  socket.on('votar', ({ opcionId, usuarioId }) => {

    // Si el usuario ya votó, no hacemos nada
    if (encuesta.usuariosVotaron.includes(usuarioId)) {
      return;
    }

    // Si la opción es válida, sumamos el voto
    if (encuesta.opciones[opcionId]) {
      encuesta.opciones[opcionId].votos += 1;
      encuesta.usuariosVotaron.push(usuarioId);

      // 3. Avisar a TODOS los conectados del cambio
      io.emit('actualizar_encuesta', encuesta);
    }
  });
});

// Arrancar el servidor en puerto 4000
const PORT = 4000;
server.listen(PORT, () => {
  console.log(`✅ Servidor Backend corriendo en http://localhost:${PORT}`);
});
