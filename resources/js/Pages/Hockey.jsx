import React from "react";
import AppLayer from "@/Components/AppLayer";
import { Head } from "@inertiajs/react";

const Hockey = () => {
    return (
        <div>
            <Head title="Hockey" />

            <AppLayer selectedSport={"Hockey"} />
        </div>
    );
};

export default Hockey;
