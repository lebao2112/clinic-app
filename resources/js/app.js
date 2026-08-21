import './bootstrap';
import { createApp } from 'vue';
import ElementPlus from 'element-plus';
import '../css/app.css'; 
import router from './router';
import App from './App.vue'; 

const app = createApp(App);

app.use(ElementPlus);
app.use(router);
app.mount('#app');