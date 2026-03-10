import './stimulus_bootstrap.js';
import './styles/app.css';

function setCookie(name, value, days) {
    const expires = new Date();
    expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
    document.cookie = name + '=' + encodeURIComponent(value) + ';expires=' + expires.toUTCString() + ';path=/;SameSite=Lax';
}

function getCookie(name) {
    const nameEQ = name + '=';
    const ca = document.cookie.split(';');
    for (let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) === ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0) {
            return decodeURIComponent(c.substring(nameEQ.length, c.length));
        }
    }
    return null;
}

function deleteCookie(name) {
    document.cookie = name + '=;expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/;';
}

window.setToken = function(token) {
    let days = 7;
    try {
        const payload = JSON.parse(atob(token.split('.')[1]));
        if (payload.exp) {
            const expDate = new Date(payload.exp * 1000);
            const now = new Date();
            const diffMs = expDate - now;
            const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));
            if (diffDays > 0) {
                days = diffDays;
            }
        }
    } catch (e) {
        // If decode fails, use default 7 days
    }
    setCookie('jwt_token', token, days);
    if (window.updateNavbar) window.updateNavbar();
};

window.getToken = function() {
    return getCookie('jwt_token');
};

window.removeToken = function() {
    deleteCookie('jwt_token');
    if (window.updateNavbar) window.updateNavbar();
};

window.decodeJWT = function(token) {
    try {
        const base64Url = token.split('.')[1];
        const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
        const jsonPayload = decodeURIComponent(atob(base64).split('').map(function(c) {
            return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
        }).join(''));
        return JSON.parse(jsonPayload);
    } catch (e) {
        return null;
    }
};

window.apiRequest = async function(url, options = {}) {
    const token = window.getToken();
    const headers = {
        'Content-Type': 'application/json',
        ...options.headers
    };
    
    if (token) {
        headers['Authorization'] = `Bearer ${token}`;
    }

    const response = await fetch(url, {
        ...options,
        headers
    });

    if (response.status === 401) {
        window.removeToken();
        window.location.href = '/login';
        return null;
    }

    return response;
};
