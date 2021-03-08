export default function bar_path(x, y_lower, y_upper) {
    const bar_width = 6;
    const x_left = (x - (bar_width / 2));
    const x_right = (x + (bar_width / 2));

    const p = d3.path();
    // vertical line
    p.moveTo(x, y_upper);
    p.lineTo(x, y_lower);
    // bottom bar
    p.moveTo(x_left, y_lower);
    p.lineTo(x_right, y_lower);
    // top bar
    p.moveTo(x_left, y_upper);
    p.lineTo(x_right, y_upper);

    return p.toString();
};
