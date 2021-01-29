export default function regression_line(data, x_acc, y_acc, skip_predicate) {

    let [count, sum_x, sum_y, sum_xy, sum_xx, sum_yy] = [0, 0, 0, 0, 0, 0];
    for (const datum of data) {
        if (skip_predicate(datum)) continue;
        count++;
        sum_x += x_acc(datum);
        sum_y += y_acc(datum);
        sum_xy += x_acc(datum) * y_acc(datum);
        sum_xx += x_acc(datum) ** 2;
        sum_yy += y_acc(datum) ** 2;
    }
    const top = (count * sum_xy) - (sum_x * sum_y);
    const bottom = (count * sum_xx) - (sum_x ** 2);
    const slope = top / bottom;
    const intercept =
        (sum_y - (slope * sum_x)) / count;
    const r_squared =
        (top /
            Math.sqrt(bottom * (count * sum_yy - sum_y ** 2))
        ) ** 2;
    return [slope, intercept, r_squared];
}

