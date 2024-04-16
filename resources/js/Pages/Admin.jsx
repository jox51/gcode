import React from "react";
import AppLayer from "@/Components/AppLayer";
import { Head } from "@inertiajs/react";

const Main = () => {
    return (
        <div>
            <Head title="Main" />

            <AppLayer selectedSport={"Main"} />
        </div>
    );
};

export default Main;
