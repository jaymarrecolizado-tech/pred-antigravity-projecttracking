/**
 * Map Color & Marker Utilities for DICT NMS-V2 Cartography
 */

/**
 * 3-Point Color Interpolator:
 * <= 20%: Crimson Red (#ef4444)
 * ~50-70%: Amber Yellow (#f59e0b)
 * >= 95%: Emerald Green (#10b981)
 *
 * @param {number} pct Percentage 0 to 100
 * @returns {string} Hex color string
 */
export function getPercentageColor(pct) {
    const p = Math.max(0, Math.min(100, Number(pct) || 0));

    if (p <= 20) {
        return '#ef4444'; // Crimson Red
    }
    if (p >= 95) {
        return '#10b981'; // Emerald Green
    }
    if (p < 55) {
        // Interpolate between Red (#ef4444) and Yellow (#f59e0b)
        const t = (p - 20) / 35;
        return interpolateHex('#ef4444', '#f59e0b', t);
    }
    // Interpolate between Yellow (#f59e0b) and Green (#10b981)
    const t = (p - 55) / 40;
    return interpolateHex('#f59e0b', '#10b981', t);
}

function interpolateHex(hex1, hex2, factor) {
    const c1 = parseInt(hex1.replace('#', ''), 16);
    const c2 = parseInt(hex2.replace('#', ''), 16);

    const r1 = (c1 >> 16) & 255;
    const g1 = (c1 >> 8) & 255;
    const b1 = c1 & 255;

    const r2 = (c2 >> 16) & 255;
    const g2 = (c2 >> 8) & 255;
    const b2 = c2 & 255;

    const r = Math.round(r1 + factor * (r2 - r1));
    const g = Math.round(g1 + factor * (g2 - g1));
    const b = Math.round(b1 + factor * (b2 - b1));

    return `#${((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1)}`;
}

/**
 * Build Leaflet DivIcon for a cluster bubble
 */
export function createClusterIcon(L, count, percentage) {
    const bg = getPercentageColor(percentage);
    // Sleek, compact cluster sizing that doesn't obstruct regional maps:
    // < 10: 28px, 10-99: 35px, 100+: 42px
    const size = count < 10 ? 28 : (count < 100 ? 35 : 42);

    const html = `
        <div class="nms-cluster-bubble" style="
            width: ${size}px;
            height: ${size}px;
            background: ${bg};
            box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.95), 0 2px 8px rgba(0, 0, 0, 0.3);
        ">
            <div class="bubble-count">${count}</div>
            <div class="bubble-pct">${Math.round(percentage)}%</div>
        </div>
    `;

    return L.divIcon({
        html: html,
        className: 'custom-cluster-wrapper',
        iconSize: [size, size],
        iconAnchor: [size / 2, size / 2],
    });
}

/**
 * Build Leaflet DivIcon for an individual project site marker
 */
export function createSiteIcon(L, shape = 'circle', color = '#2563eb', isOperational = true) {
    const ringColor = isOperational ? '#10b981' : '#f59e0b';
    let borderRadius = '50%';

    if (shape === 'square') {
        borderRadius = '3px';
    } else if (shape === 'diamond') {
        borderRadius = '2px';
    }

    const transform = shape === 'diamond' ? 'rotate(45deg)' : 'none';

    const html = `
        <div class="nms-site-marker" style="
            width: 13px;
            height: 13px;
            background: ${color};
            border-radius: ${borderRadius};
            transform: ${transform};
            border: 1.5px solid #ffffff;
            box-shadow: 0 0 0 1.5px ${ringColor}, 0 2px 6px rgba(0,0,0,0.3);
        "></div>
    `;

    return L.divIcon({
        html: html,
        className: 'custom-site-wrapper',
        iconSize: [13, 13],
        iconAnchor: [6.5, 6.5],
    });
}
