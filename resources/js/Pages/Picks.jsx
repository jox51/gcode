import React from "react";
import AppLayer from "@/Components/AppLayer";
import { Head } from "@inertiajs/react";

const Picks = () => {
    return (
        <div>
            <Head title="Picks" />

            <AppLayer selectedSport={"Baseball"} />
        </div>
    );
};

export default Picks;
