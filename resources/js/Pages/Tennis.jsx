import React from "react";
import AppLayer from "@/Components/AppLayer";
import { Head } from "@inertiajs/react";

const Tennis = () => {
    return (
        <div>
            <Head title="Tennis" />

            <AppLayer selectedSport={"Tennis"} />
        </div>
    );
};

export default Tennis;
