export default function get_locations_str(data) {
    const coord_delim = ",";
    const point_delim = "/";
    const rect_delim = "_";

    let loc_str = "";
    data.forEach(element => {
        if (loc_str.length > 0) loc_str += rect_delim;
        loc_str += element.geometry.coordinates[0][0][0] + coord_delim;
        loc_str += element.geometry.coordinates[0][0][1] + point_delim;
        loc_str += element.geometry.coordinates[0][2][0] + coord_delim;
        loc_str += element.geometry.coordinates[0][2][1];
    });

    return loc_str;
}