export default function coord_rect_area(path) {
    const MEAN_EARTH_RADIUS_KM = 6371;

    if (path.length === 1) path = path[0];
    if (path.length < 2) return 0;

    let min_lat = 90, max_lat = -90;
    let min_lon = 180, max_lon = -180;
    for (const p of path) {
        const [lon, lat] = p;

        if (lat < min_lat) min_lat = lat;
        else if (lat > max_lat) max_lat = lat;

        if (lon < min_lon) min_lon = lon;
        else if (lon > max_lon) max_lon = lon;
    }

    if (min_lon >= max_lon || min_lat >= max_lat) return 0;

    const sin_lat_diff = Math.sin(max_lat * Math.PI / 180) - Math.sin(min_lat * Math.PI / 180);
    const polar_cap_area_diff = sin_lat_diff * 2 * Math.PI * MEAN_EARTH_RADIUS_KM ** 2;
    const circle_frac_diff = (max_lon - min_lon) / 360;

    const result = {
        lat_min: min_lat,
        lat_max: max_lat,
        lon_min: min_lon,
        lon_max: max_lon,
        area: polar_cap_area_diff * circle_frac_diff
    };
    return result;
}