import './bootstrap';
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

import Swal from 'sweetalert2';
window.Swal = Swal;

// Configuración global de SweetAlert2 para Cartify
const CartifySwal = Swal.mixin({
    customClass: {
        popup: 'cartify-swal-popup',
        title: 'cartify-swal-title',
        confirmButton: 'btn-cartify-primary px-4 py-2',
        cancelButton: 'btn-cartify-secondary px-4 py-2 ms-2',
    },
    buttonsStyling: false,
    background: 'var(--color-surface)',
    color: 'var(--color-text)',
    confirmButtonColor: 'var(--color-primary)',
});

window.CartifySwal = CartifySwal;

// Helper para Toasts
window.Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    background: 'var(--color-surface)',
    color: 'var(--color-text)',
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer)
        toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
});
