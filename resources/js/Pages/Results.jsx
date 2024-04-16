import React from "react";
import AppLayer from "@/Components/AppLayer";
import { Head } from "@inertiajs/react";

const Results = () => {
    return (
        <div>
            <Head title="Results" />

            <AppLayer selectedSport={"Results"} />
        </div>
    );
};

export default Results;
