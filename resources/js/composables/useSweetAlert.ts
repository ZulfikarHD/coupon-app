import Swal from 'sweetalert2';

export function useSweetAlert() {
    /**
     * Show a success alert
     */
    const success = (message: string, title = 'Berhasil!') => {
        return Swal.fire({
            icon: 'success',
            title,
            text: message,
            confirmButtonText: 'OK',
            confirmButtonColor: '#10b981',
            timer: 3000,
            timerProgressBar: true,
        });
    };

    /**
     * Show an error alert
     */
    const error = (message: string, title = 'Terjadi Kesalahan!') => {
        return Swal.fire({
            icon: 'error',
            title,
            text: message,
            confirmButtonText: 'OK',
            confirmButtonColor: '#ef4444',
        });
    };

    /**
     * Show a warning alert
     */
    const warning = (message: string, title = 'Peringatan!') => {
        return Swal.fire({
            icon: 'warning',
            title,
            text: message,
            confirmButtonText: 'OK',
            confirmButtonColor: '#f59e0b',
        });
    };

    /**
     * Show an info alert
     */
    const info = (message: string, title = 'Informasi') => {
        return Swal.fire({
            icon: 'info',
            title,
            text: message,
            confirmButtonText: 'OK',
            confirmButtonColor: '#3b82f6',
        });
    };

    /**
     * Show a confirmation dialog
     */
    const confirm = (
        message: string,
        title = 'Konfirmasi',
        confirmText = 'Ya',
        cancelText = 'Tidak',
        confirmColor = '#ef4444',
    ) => {
        return Swal.fire({
            icon: 'question',
            title,
            text: message,
            showCancelButton: true,
            confirmButtonText: confirmText,
            cancelButtonText: cancelText,
            confirmButtonColor: confirmColor,
            cancelButtonColor: '#6b7280',
            reverseButtons: true,
        });
    };

    /**
     * Show a prompt dialog
     */
    const prompt = (
        message: string,
        title = 'Input',
        inputPlaceholder = '',
        inputValue = '',
        confirmText = 'OK',
        cancelText = 'Batal',
    ) => {
        return Swal.fire({
            title,
            text: message,
            input: 'text',
            inputPlaceholder,
            inputValue,
            showCancelButton: true,
            confirmButtonText: confirmText,
            cancelButtonText: cancelText,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280',
            reverseButtons: true,
            inputValidator: (value) => {
                if (!value) {
                    return 'Input tidak boleh kosong!';
                }
            },
        });
    };

    /**
     * Show a toast notification (non-blocking)
     */
    const toast = (
        message: string,
        icon: 'success' | 'error' | 'warning' | 'info' = 'success',
        duration = 3000,
    ) => {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: duration,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            },
        });

        return Toast.fire({
            icon,
            title: message,
        });
    };

    /**
     * Show loading state
     */
    const loading = (message = 'Memproses...') => {
        return Swal.fire({
            title: message,
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });
    };

    /**
     * Close any open SweetAlert
     */
    const close = () => {
        Swal.close();
    };

    return {
        success,
        error,
        warning,
        info,
        confirm,
        prompt,
        toast,
        loading,
        close,
        // Expose Swal for advanced usage
        Swal,
    };
}
