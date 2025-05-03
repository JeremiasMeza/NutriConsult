// preload.js
const { ipcRenderer } = require('electron');

// Exponer ipcRenderer al proceso de renderizado
window.ipcRenderer = ipcRenderer;