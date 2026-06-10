import axios from 'axios';

if (typeof window !== 'undefined') {
    window.axios = axios;
    window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

    window.axios.defaults.withCredentials = true;      // ← add this
    window.axios.defaults.withXSRFToken = true;        // ← add this
}
