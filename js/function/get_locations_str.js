export default function get_locations_str(data) {
    var coord_delim = ",";
    var point_delim = "/";
    var rect_delim = "_";

    var loc_str = "";
    data.forEach(element => {
        if (loc_str.length > 0) loc_str += rect_delim;
        loc_str += element.geometry.coordinates[0][0][0] + coord_delim;
        loc_str += element.geometry.coordinates[0][0][1] + point_delim;
        loc_str += element.geometry.coordinates[0][2][0] + coord_delim;
        loc_str += element.geometry.coordinates[0][2][1];
    });

    return loc_str;
}