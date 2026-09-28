import axios from "https://cdn.jsdelivr.net/npm/axios@1.11.0/+esm";

export const API = axios.create({
    baseURL: "http://localhost:8000",
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