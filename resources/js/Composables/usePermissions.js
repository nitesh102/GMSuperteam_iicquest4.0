import { usePage } from '@inertiajs/vue3';

export function usePermission() {
    const page = usePage();

    const permissions = page.props.auth?.permissions || [];
    const roles = page.props.auth?.roles || [];

    const can = (permission) => {
        return permissions.includes(permission);
    };

    const canAny = (permissionList = []) => {
        return permissionList.some(p => permissions.includes(p));
    };

    const canAll = (permissionList = []) => {
        return permissionList.every(p => permissions.includes(p));
    };

    const hasRole = (role) => {
        return roles.includes(role);
    };

    const hasAnyRole = (roleList = []) => {
        return roleList.some(r => roles.includes(r));
    };

    return {
        can,
        canAny,
        canAll,
        hasRole,
        hasAnyRole,
    };
}
