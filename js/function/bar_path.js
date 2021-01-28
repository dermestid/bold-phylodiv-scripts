export default function bar_path(ci_, x_, y, yax) {
    const bar_width = 10;
    const x = {
        middle: x_,
        left: (x_ - (bar_width / 2)),
        right: (x_ + (bar_width / 2))
    };
    const ci = ci_.interval ?
        {
            upper: ci_.upper,
            lower: ci_.lower
        } : {
            upper: y + ci_.sd,
            lower: y - ci_.sd
        };
    const p = d3.path();
    // vertical line
    p.moveTo(x.middle, yax(ci.upper));
    p.lineTo(x.middle, yax(ci.lower));
    // bottom bar
    p.moveTo(x.left, yax(ci.lower));
    p.lineTo(x.right, yax(ci.lower));
    // top bar
    p.moveTo(x.left, yax(ci.upper));
    p.lineTo(x.right, yax(ci.upper));

    return p.toString();
};