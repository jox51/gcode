import React from "react";
import AppLayer from "@/Components/AppLayer";
import { Head } from "@inertiajs/react";

const ResultsMonthly = () => {
    return (
        <div>
            <Head title="Results" />

            <AppLayer selectedSport={"ResultsMonthly"} />
        </div>
    );
};

export default ResultsMonthly;
