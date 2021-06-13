const Toast = {
    methods: {
        toast(icon, title, options = {}) {
            SwalToast.fire({
                icon,
                title,
                ...options
            })
        }
    }
}

export default Toast;
