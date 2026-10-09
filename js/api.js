import axios from "https://cdn.jsdelivr.net/npm/axios@1.11.0/+esm";

export const API = axios.create({
<<<<<<< HEAD
    baseURL: "http://localhost:8000",
=======
    baseURL: window.location.origin + "/ERP-SYSTEM-FOR-COACHING",
>>>>>>> ae921aca951a77a64057d77aecbabb0efc7f2460
    headers: {
        "Content-Type": "application/json"
    },
    timeout: 10000
});

export async function getData(endpoint) {
    const response = await API.get(endpoint);
    return response.data;
}

export async function postData(endpoint, data) {
    const response = await API.post(endpoint, data);
    return response.data;
}