import { useMemo } from "react";
import { MaterialReactTable } from "material-react-table";
import { useThemeStore } from "@/store/themeStore"; // Adjust the import path as necessary
import { useTheme } from "@mui/material";
import { usePage } from "@inertiajs/react";

const TennisTable = () => {
    const { tennisGames } = usePage().props;
    const { darkMode } = useThemeStore();
    const theme = useTheme();

    const darkBackgroundColor = "#212020"; // Example of a darker gray for dark mode

    // Adjust the mrtTheme based on the darkMode state
    const mrtTheme = {
        baseBackgroundColor: darkMode
            ? darkBackgroundColor
            : theme.palette.background.paper,
    };

    const tableData = tennisGames
        .filter((game) => game.to_win !== "" || game.auto_over !== 0) // First, filter out games where `to_win` is empty
        .map((game) => {
            // Then, map over the filtered games to transform them
            const player1Data = JSON.parse(game.player1);
            const player2Data = JSON.parse(game.player2);
            const formattedDate = new Date(game.date).toLocaleDateString(
                "en-US",
                {
                    month: "numeric", // numeric, 2-digit, long, short, narrow
                    day: "numeric", // numeric, 2-digit
                    year: "numeric", // numeric, 2-digit
                }
            );

            return {
                ...game,
                start_date: formattedDate,
                player1Data,
                player2Data,
            };
        });

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

    const columns = useMemo(
        () => [
            {
                accessorKey: "start_date", //access nested data with dot notation
                header: "Date",
            },
            {
                accessorFn: (row) =>
                    `${row.player1Data.name} vs ${row.player2Data.name}`,
                id: "match",
                header: "Match",
            },
            {
                accessorFn: (row) =>
                    row.auto_over === 1
                        ? "Over"
                        : row.to_win === "home"
                        ? `${row.player2Data.name} to win`
                        : `${row.player1Data.name} to win`,
                id: "toWin",
                header: "Bet",
            },
            {
                accessorKey: "algo_rank",
                header: "G-Code",
                Cell: ({ cell }) => {
                    const rank = cell.getValue();
                    return (
                        <div
                            style={{
                                display: "inline-block",
                                backgroundColor: algoRankColor(rank),
                                color: "#fff",
                                padding: "4px 12px", // Increased horizontal padding
                                minWidth: "36px", // Minimum width for the div
                                borderRadius: "18px", // Increase for more rounded edges if desired
                                textTransform: "uppercase", // Capitalize the letter
                                fontWeight: "bold",
                                textAlign: "center", // Ensure the content is centered, especially useful if you use minWidth
                            }}
                        >
                            {rank}
                        </div>
                    );
                },
            },
        ],
        []
    );

    return (
        <MaterialReactTable
            columns={columns}
            data={tableData}
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

export default TennisTable;
