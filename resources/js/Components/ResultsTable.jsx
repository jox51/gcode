import { useMemo } from "react";
import { MaterialReactTable } from "material-react-table";
import { useThemeStore } from "@/store/themeStore"; // Adjust the import path as necessary
import { useTheme } from "@mui/material";
import { usePage } from "@inertiajs/react";

const ResultsTable = () => {
    const { last30DaysSummaries } = usePage().props;
    const { darkMode } = useThemeStore();
    const theme = useTheme();

    const parseResults = (results) => JSON.parse(results);

    // Function to check if there are actual values for a rank across all summaries
    const hasValidResults = (rank, summaries) => {
        return summaries.some((summary) => {
            const results = parseResults(
                summary[`${rank.toLowerCase()}_results`]
            );
            return results.total > 0;
        });
    };

    // Identify which ranks have valid results to display
    const validRanks = ["A", "B", "C", "D", "E", "F", "G", "H", "I"].filter(
        (rank) => hasValidResults(rank, last30DaysSummaries)
    );

    const columns = useMemo(
        () => [
            {
                accessorKey: "date",
                header: "Date",
                Cell: ({ cell }) => cell.getValue(),
            },
            {
                accessorKey: "sport",
                header: "Sport",
            },
            // Dynamically generate columns for each valid algo_rank result
            ...validRanks.map((rank) => ({
                id: `${rank.toLowerCase()}_results`,
                header: `${rank} Results`,
                Cell: ({ row }) => {
                    const data = parseResults(
                        row.original[`${rank.toLowerCase()}_results`]
                    );
                    return `${data.correct} / ${data.total} (${data.percentage}%)`;
                },
            })),
            {
                id: "total_results",
                header: "Total Results",
                Cell: ({ row }) => {
                    const overall = parseResults(row.original.total_results);
                    return `Correct: ${overall.correct}, Total: ${overall.total}, Percentage: ${overall.percentage}%`;
                },
            },
        ],
        [last30DaysSummaries] // Depend on last30DaysSummaries to recalculate when it changes
    );

    const darkBackgroundColor = "#212020"; // Example of a darker gray for dark mode

    // Adjust the mrtTheme based on the darkMode state
    const mrtTheme = {
        baseBackgroundColor: darkMode
            ? darkBackgroundColor
            : theme.palette.background.paper,
    };

    const algoRankColor = (rank) => {
        const colors = {
            A: "#4CAF50", // Green
            B: "#8BC34A",
            C: "#CDDC39",
            D: "#FFEB3B",
            E: "#FFC107",
            F: "#FF9800",
            G: "#FF5722",
            H: "#F44336", // Red
            I: "#E91E63",
            J: "#9C27B0",
        };

        return colors[rank.toUpperCase()] || "#9E9E9E"; // Default to grey if undefined
    };

    return (
        <MaterialReactTable
            columns={columns}
            data={last30DaysSummaries}
            enableStickyHeader
            muiTableContainerProps={{
                sx: {
                    maxHeight: "calc(100vh - 64px)", // adjust based on your layout
                },
            }}
            mrtTheme={{
                baseBackgroundColor: mrtTheme.baseBackgroundColor,
                draggingBorderColor: theme.palette.secondary.main,
            }}
        />
    );
};

export default ResultsTable;
