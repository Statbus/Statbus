import { createApp } from "vue";
import App from "./App.vue";

const mountEl = document.getElementById("app");
const loadUri = mountEl?.dataset?.loadUri || null;

createApp(App, { loadUri }).mount(mountEl);
