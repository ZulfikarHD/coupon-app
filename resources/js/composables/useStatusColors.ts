import { computed } from 'vue';

export interface StatusColorConfig {
    bg: string;
    text: string;
    border: string;
    icon?: string;
}

export const statusColors = {
    active: {
        bg: 'bg-green-50 dark:bg-green-950/30',
        text: 'text-green-700 dark:text-green-400',
        border: 'border-green-200 dark:border-green-800/50',
        icon: 'text-green-600 dark:text-green-400',
    },
    used: {
        bg: 'bg-gray-50 dark:bg-gray-900/30',
        text: 'text-gray-700 dark:text-gray-400',
        border: 'border-gray-200 dark:border-gray-800/50',
        icon: 'text-gray-600 dark:text-gray-400',
    },
    expired: {
        bg: 'bg-red-50 dark:bg-red-950/30',
        text: 'text-red-700 dark:text-red-400',
        border: 'border-red-200 dark:border-red-800/50',
        icon: 'text-red-600 dark:text-red-400',
    },
} as const;

export const roleColors = {
    admin: {
        bg: 'bg-purple-50 dark:bg-purple-950/30',
        text: 'text-purple-700 dark:text-purple-400',
        border: 'border-purple-200 dark:border-purple-800/50',
        icon: 'text-purple-600 dark:text-purple-400',
    },
    user: {
        bg: 'bg-blue-50 dark:bg-blue-950/30',
        text: 'text-blue-700 dark:text-blue-400',
        border: 'border-blue-200 dark:border-blue-800/50',
        icon: 'text-blue-600 dark:text-blue-400',
    },
} as const;

export const statusLabels = {
    active: 'Aktif',
    used: 'Terpakai',
    expired: 'Kedaluwarsa',
} as const;

export const roleLabels = {
    admin: 'Admin',
    user: 'User',
} as const;

export function useStatusColors() {
    const getStatusColor = (status: keyof typeof statusColors) => {
        return statusColors[status];
    };

    const getRoleColor = (role: keyof typeof roleColors) => {
        return roleColors[role];
    };

    const getStatusBadgeClasses = (status: keyof typeof statusColors) => {
        const colors = statusColors[status];
        return `${colors.bg} ${colors.text} ${colors.border} border`;
    };

    const getRoleBadgeClasses = (role: keyof typeof roleColors) => {
        const colors = roleColors[role];
        return `${colors.bg} ${colors.text} ${colors.border} border`;
    };

    return {
        statusColors,
        roleColors,
        statusLabels,
        roleLabels,
        getStatusColor,
        getRoleColor,
        getStatusBadgeClasses,
        getRoleBadgeClasses,
    };
}
