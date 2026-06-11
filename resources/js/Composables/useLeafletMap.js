import L from 'leaflet'
import 'leaflet/dist/leaflet.css'

import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png'
import markerIcon from 'leaflet/dist/images/marker-icon.png'
import markerShadow from 'leaflet/dist/images/marker-shadow.png'

delete L.Icon.Default.prototype._getIconUrl
L.Icon.Default.mergeOptions({
    iconRetinaUrl: markerIcon2x,
    iconUrl: markerIcon,
    shadowUrl: markerShadow,
})

const DEFAULT_BOUNDS = [[20, 75], [33, 92]]

export function useLeafletMap() {
    function createMap(container, options = {}) {
        const map = L.map(container, {
            center: options.center || [28.3949, 84.1240],
            zoom: options.zoom || 7,
            zoomControl: options.zoomControl !== false,
            maxBounds: DEFAULT_BOUNDS,
            maxBoundsViscosity: 1,
        })

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19,
        }).addTo(map)

        return map
    }

    return { L, createMap }
}
