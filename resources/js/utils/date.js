export function formatDisplayDate(value) {
    if (!value) {
        return '';
    }

    if (/^\d{2}-\d{2}-\d{4}$/.test(value)) {
        return value;
    }

    const normalized = String(value).includes('T') ? value : `${value}T00:00:00`;
    const date = new Date(normalized);

    if (Number.isNaN(date.getTime())) {
        return String(value);
    }

    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();

    return `${day}-${month}-${year}`;
}

export function formatTrendLabel(value, granularity = 'day') {
    if (!value) {
        return '';
    }

    if (granularity === 'hour') {
        const date = new Date(String(value).replace(' ', 'T'));

        if (Number.isNaN(date.getTime())) {
            return String(value);
        }

        const hours24 = date.getHours();
        const minutes = String(date.getMinutes()).padStart(2, '0');
        const period = hours24 >= 12 ? 'PM' : 'AM';
        const hours12 = hours24 % 12 || 12;

        return `${hours12}:${minutes} ${period}`;
    }

    return formatDisplayDate(value);
}

export function formatDisplayDateTime(value) {
    if (!value) {
        return '';
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return String(value);
    }

    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');

    return `${day}-${month}-${year} ${hours}:${minutes}`;
}
