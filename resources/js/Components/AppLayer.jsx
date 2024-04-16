import { useEffect } from "react";
import BaseballTable from "@/Components/BaseballTable";
import HockeyTable from "./HockeyTable";
import TennisTable from "./TennisTable";
import ResultsTable from "./ResultsTable";
import ResultsMonthly from "@/Pages/ResultsMonthly";
import ResultsMonthlyTable from "./ResultsMonthlyTable";
import AdminComponent from "./AdminComponent";
// import SoccerTable from "./SoccerTable";
// import TennisTable from "./TennisTable";
// import BaseballTable from "./BaseballTable";
// import HandballTable from "./HandballTable";

function classNames(...classes) {
    return classes.filter(Boolean).join(" ");
}

export default function AppLayer({ games, selectedSport }) {
    return (
        <>
            <div className="min-h-full">
                <div className="py-10 dark:bg-gray-700">
                    <header>
                        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:p-8 "></div>
                    </header>
                    <main>
                        <div className="mx-auto max-w-7xl sm:px-6 lg:px-8 py-8 ">
                            {selectedSport === "Main" && <AdminComponent />}
                            {selectedSport === "Baseball" && <BaseballTable />}
                            {selectedSport === "Hockey" && <HockeyTable />}
                            {selectedSport === "Tennis" && <TennisTable />}
                            {selectedSport === "Results" && <ResultsTable />}
                            {selectedSport === "ResultsMonthly" && (
                                <ResultsMonthlyTable />
                            )}

                            {/* {selectedSport === "NBA" && (
                                <NBATable games={games} />
                            )}
                            {selectedSport === "Soccer" && <SoccerTable />}
                            {selectedSport === "Tennis" && <TennisTable />}
                            {selectedSport === "Baseball" && <BaseballTable />}
                            {selectedSport === "Handball" && <HandballTable />} */}
                        </div>
                    </main>
                </div>
            </div>
        </>
    );
}
