// resources/js/Config/menuPermissions.js

export const ROLES = {
    ADMIN: 'admin',
    SYAHBANDAR: 'syahbandar',
    PETUGAS: 'petugas',
    KEPALA_PELABUHAN: 'kepala_pelabuhan',
};

// All roles for convenience
const ALL_ROLES = [ROLES.ADMIN, ROLES.SYAHBANDAR, ROLES.PETUGAS, ROLES.KEPALA_PELABUHAN];
const ADMIN_SYAHBANDAR = [ROLES.ADMIN, ROLES.SYAHBANDAR];
const NOT_KEPALA_PELABUHAN = [ROLES.ADMIN, ROLES.SYAHBANDAR, ROLES.PETUGAS];

export const menuConfig = [
    {
        title: 'Dashboard',
        icon: 'ri-dashboard-line',
        to: '/',
        roles: ALL_ROLES
    },
    {
        title: 'Posisi Kapal',
        icon: 'ri-map-pin-2-line',
        to: '/vessel-positions',
        roles: ALL_ROLES
    },
    {
        title: 'Data',
        icon: 'ri-database-2-line',
        roles: ALL_ROLES,
        items: [
            { title: 'Jenis Ikan', icon: 'ri-deepseek-line', to: '/fish-species', roles: ALL_ROLES },
            { title: 'Dermaga', icon: 'ri-anchor-line', to: '/landing-sites', roles: ALL_ROLES },
        ]
    },
    {
        title: 'Manajemen Kapal',
        icon: 'ri-ship-line',
        roles: ALL_ROLES,
        items: [
            { title: 'Daftar Kapal', icon: 'ri-ship-2-line', to: '/vessels', roles: ALL_ROLES },
            { title: 'Approval Kapal', icon: 'ri-check-double-line', to: '/vessels/approval', roles: NOT_KEPALA_PELABUHAN },
        ]
    },
    {
        title: 'Kedatangan',
        icon: 'ri-anchor-line',
        roles: ALL_ROLES,
        items: [
            { title: 'Daftar Kedatangan', icon: 'ri-anchor-line', to: '/arrivals', roles: ALL_ROLES },
            { title: 'Tambah Kedatangan', icon: 'ri-add-circle-line', to: '/arrivals/create', roles: NOT_KEPALA_PELABUHAN },
        ]
    },
    {
        title: 'Keberangkatan',
        icon: 'ri-sailboat-line',
        roles: ALL_ROLES,
        items: [
            { title: 'Daftar Keberangkatan', icon: 'ri-sailboat-line', to: '/departures', roles: ALL_ROLES },
            { title: 'Tambah Keberangkatan', icon: 'ri-add-circle-line', to: '/departures/create', roles: NOT_KEPALA_PELABUHAN },
            { title: 'Permohonan SPR', icon: 'ri-file-info-line', to: '/spr-departures', roles: NOT_KEPALA_PELABUHAN },
        ]
    },
    {
        title: 'Penimbangan Ikan',
        icon: 'ri-download-cloud-2-line',
        roles: ALL_ROLES,
        items: [
            { title: 'Daftar Penimbangan', icon: 'ri-list-check', to: '/unloadings', roles: ALL_ROLES },
            { title: 'Approval Penimbangan', icon: 'ri-check-double-line', to: '/approval', roles: [ROLES.SYAHBANDAR] },
        ]
    },
    {
        title: 'Dokumen',
        icon: 'ri-file-list-3-line',
        to: '/documents',
        roles: ALL_ROLES
    },
    {
        title: 'Laporan',
        icon: 'ri-bar-chart-line',
        roles: ALL_ROLES,
        items: [
            { title: 'Laporan Kedatangan', icon: 'ri-bar-chart-grouped-line', to: '/reports/arrivals', roles: ALL_ROLES },
            { title: 'Laporan Keberangkatan', icon: 'ri-bar-chart-grouped-line', to: '/reports/departures', roles: ALL_ROLES },
            { title: 'Laporan Data Kapal', icon: 'ri-ship-2-line', to: '/reports/vessels', roles: ALL_ROLES },
            { title: 'Laporan Tangkapan', icon: 'ri-bar-chart-grouped-line', to: '/reports/catches', roles: ALL_ROLES },
        ]
    },
    {
        title: 'Manajemen User',
        icon: 'ri-user-settings-line',
        to: '/users',
        roles: [ROLES.ADMIN]
    },
    {
        title: 'Settings',
        icon: 'ri-settings-4-line',
        to: '/settings',
        roles: ROLES.ADMIN
    }
];
