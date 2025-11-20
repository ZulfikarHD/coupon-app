import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import { useSweetAlert } from '@/composables/useSweetAlert';

/**
 * Global flash message handler using SweetAlert2
 * This plugin watches for flash messages from Laravel and displays them as SweetAlert2 toasts
 */
export function setupSweetAlertFlash() {
    const page = usePage();
    const swal = useSweetAlert();

    // Watch for flash success messages
    watch(
        () => page.props.flash?.success as string | undefined,
        (message) => {
            if (message) {
                swal.toast(message, 'success');
            }
        },
    );

    // Watch for flash error messages
    watch(
        () => page.props.flash?.error as string | undefined,
        (message) => {
            if (message) {
                swal.toast(message, 'error');
            }
        },
    );

    // Watch for flash warning messages
    watch(
        () => page.props.flash?.warning as string | undefined,
        (message) => {
            if (message) {
                swal.toast(message, 'warning');
            }
        },
    );

    // Watch for flash info messages
    watch(
        () => page.props.flash?.info as string | undefined,
        (message) => {
            if (message) {
                swal.toast(message, 'info');
            }
        },
    );
}
