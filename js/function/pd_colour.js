import map_colours from "./map_colours.js";

export default function pd_colour(pd, multiplier = 1, exp_base = 0) {
    var base_multiplier = 50;

    // pd varies with number of branches/taxa in subsample, so correct
    var pd_adjusted = base_multiplier * multiplier * pd / $("#subs").val();

    // Sometimes pds are very clustered, so we may want to exponentiate
    if (exp_base > 0) pd_adjusted = Math.pow(exp_base, pd_adjusted);

    pd_adjusted = Math.floor(pd_adjusted); // Hope that this value is somewhere around 1 to 4
    var pd_level = Math.min(pd_adjusted, (map_colours().length - 1));
    return map_colours()[pd_level];
}

// returns the pd intervals - basically the inverse image of the above function
function pd_levels(multiplier = 1, exp_base = 0) {

    var range = map_colours().keys();

    if (exp_base > 0) range = range.map(function (val) {
        return Math.log(val) / Math.log(exp_base);
    });

    if (multiplier != 0) range = range.map(function (val) {
        return val * $("#subs").val() / (multiplier * base_multiplier);
    });

    return range;
}