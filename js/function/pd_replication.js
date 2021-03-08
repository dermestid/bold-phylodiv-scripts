import get_pd from "./get_pd.js";
import pd_td_combine from "./pd_td_combine.js";

export default function pd_replication(replicates, id, pd_td_data, dl, tax, scheme_key, loc_str, subs, c_update) {
    if (pd_replication.cache === undefined) pd_replication.cache = {};
    if (pd_replication.cache[id] === undefined) pd_replication.cache[id] = [pd_td_data];
    else pd_replication.cache[id].push(pd_td_data);

    const reps = pd_replication.cache[id].length;

    if (reps > 1) {

        // console.log(`calculating stats for ${id} rep ${reps}`);

        const stats = pd_td_data.map(d => {
            let sum = 0;
            let vals = [];

            for (const rep of pd_replication.cache[id]) {
                for (const entry of rep) {
                    if (entry.key === d.key) {
                        const v = entry.properties.pd;
                        sum += v;
                        vals.push(v);
                        break;
                    }
                }
            }
            // console.log(`vals for ${id} rep ${reps}: ${JSON.stringify(vals)}`)
            const mean = sum / reps;
            const ssd = vals.reduce((s, v) => s + (v - mean) ** 2, 0) / (reps - 1);
            const se = ssd / Math.sqrt(reps);

            // console.log(`ssd for ${id} rep ${reps}: ${ssd}`);

            d.properties.pd_mean = mean;
            d.properties.pd_se = se;
            d.properties.pd_ci_upper = mean + se * 1.96;
            d.properties.pd_ci_lower = mean - se * 1.96;

            return d;
        });

        c_update(stats);
    }

    if (reps < replicates) {

        // console.log(`getting data for ${id} rep ${reps + 1}`);

        get_pd(
            dl,
            tax,
            scheme_key,
            subs,
            pd_update => {
                pd_replication(
                    replicates,
                    id,
                    pd_td_combine(pd_update, pd_td_data), // This argument is different, all the others are the same
                    dl,
                    tax,
                    scheme_key,
                    loc_str,
                    subs,
                    c_update);
            },
            loc_str
        );
    }
}