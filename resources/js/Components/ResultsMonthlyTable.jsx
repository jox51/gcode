import { useMemo } from "react";
import { MaterialReactTable } from "material-react-table";
import { useThemeStore } from "@/store/themeStore";
import { useTheme } from "@mui/material";
import { usePage } from "@inertiajs/react";

const ResultsMonthlyTable = () => {
    const { monthlyResults } = usePage().props;
    const { darkMode } = useThemeStore();
    const theme = useTheme();

    // Prepare table data and dynamically determine which columns to display
    const { tableData, validRanks } = useMemo(() => {
        let data = [];
        let rankCounts = {}; // Track counts of non-zero entries for each rank

        monthlyResults.forEach((result) => {
            ["baseball", "hockey", "tennis"].forEach((sport) => {
                if (result[sport]) {
                    let row = {
                        month: result.result_month,
                        sport: sport.charAt(0).toUpperCase() + sport.slice(1), // Capitalize sport name
                    };

                    ["a", "b", "c", "d", "e", "f", "g", "h", "i"].forEach(
                        (rank) => {
                            const rankData = result[sport][rank];
                            if (rankData && rankData.total > 0) {
                                row[rank] = `${rankData.percentage}%`; // Add rank with percentage
                                rankCounts[rank] = (rankCounts[rank] || 0) + 1; // Increment count for this rank
                            }
                        }
                    );

                    // Add total results
                    row.total = `${result[sport].total.percentage}%`;
                    data.push(row); // Push each sport's data as a new row
                }
            });
        });

        // Filter ranks that have any non-zero entries
        const validRanks = Object.keys(rankCounts).filter(
            (rank) => rankCounts[rank] > 0
        );

        return { tableData: data, validRanks };
    }, [monthlyResults]);

    const columns = useMemo(
        () => [
            {
                accessorKey: "month",
                header: "Month",
                Cell: ({ cell }) =>
                    new Date(cell.getValue()).toLocaleDateString(undefined, {
                        year: "numeric",
                        month: "long",
                    }),
            },
            {
                accessorKey: "sport",
                header: "Sport",
            },
            ...validRanks.map((rank) => ({
                id: rank,
                accessorKey: rank,
                header: rank.toUpperCase(),
                Cell: ({ cell }) => cell.getValue() || "N/A", // Display rank percentage or N/A
            })),
            {
                accessorKey: "total",
                header: "Total %",
            },
        ],
        [validRanks]
    );

    const mrtTheme = {
        baseBackgroundColor: darkMode
            ? "#212020"
            : theme.palette.background.paper,
    };

    return (
        <MaterialReactTable
            columns={columns}
            data={tableData}
            enableStickyHeader
            muiTableContainerProps={{
                sx: { maxHeight: "calc(100vh - 64px)" },
            }}
            mrtTheme={mrtTheme}
        />
    );
};

export default ResultsMonthlyTable;
