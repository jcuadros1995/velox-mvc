// public/js/toast.js

const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer);
        toast.addEventListener('mouseleave', Swal.resumeTimer);
    }
});

/**
 * Helper global de Toasts estandarizado con fondo cromático
 * @param {'success'|'error'|'warning'|'info'} icon
 * @param {string} title
 */
window.showToast = function(icon, title) {
    let iconColor = '#10b981';
    let bgColor = '#ecfdf5'; // Fondo verde claro
    let textColor = '#065f46'; // Texto verde oscuro

    if (icon === 'error') {
        iconColor = '#ef4444';
        bgColor = '#fef2f2';  // Fondo rojo claro
        textColor = '#991b1b'; // Texto rojo oscuro
    } else if (icon === 'warning') {
        iconColor = '#f59e0b';
        bgColor = '#fffbeb';  // Fondo amarillo claro
        textColor = '#92400e'; // Texto amarillo oscuro
    } else if (icon === 'info') {
        iconColor = '#3b82f6';
        bgColor = '#eff6ff';  // Fondo azul claro
        textColor = '#1e40af'; // Texto azul oscuro
    }

    Toast.fire({
        icon: icon,
        title: title,
        iconColor: iconColor,
        background: bgColor,
        color: textColor,
        customClass: {
            popup: `toast-custom-${icon}`
        }
    });
};