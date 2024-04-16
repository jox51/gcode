import { useMemo } from "react";
import { MaterialReactTable } from "material-react-table";
import { useThemeStore } from "@/store/themeStore"; // Adjust the import path as necessary
import { useTheme } from "@mui/material";
import { usePage } from "@inertiajs/react";

//nested data is ok, see accessorKeys in ColumnDef below
const data = [
    {
        name: {
            firstName: "John",
            lastName: "Doe",
        },
        address: "261 Erdman Ford",
        city: "East Daphne",
        state: "Kentucky",
    },
    {
        name: {
            firstName: "Jane",
            lastName: "Doe",
        },
        address: "769 Dominic Grove",
        city: "Columbus",
        state: "Ohio",
    },
    {
        name: {
            firstName: "Joe",
            lastName: "Doe",
        },
        address: "566 Brakus Inlet",
        city: "South Linda",
        state: "West Virginia",
    },
    {
        name: {
            firstName: "Kevin",
            lastName: "Vandy",
        },
        address: "722 Emie Stream",
        city: "Lincoln",
        state: "Nebraska",
    },
    {
        name: {
            firstName: "Joshua",
            lastName: "Rolluffs",
        },
        address: "32188 Larkin Turnpike",
        city: "Charleston",
        state: "South Carolina",
    },
];

const HockeyTable = () => {
    const { hockeyGames } = usePage().props;
    const { darkMode } = useThemeStore();
    const theme = useTheme();

    const darkBackgroundColor = "#212020"; // Example of a darker gray for dark mode

    // Adjust the mrtTheme based on the darkMode state
    const mrtTheme = {
        baseBackgroundColor: darkMode
            ? darkBackgroundColor
            : theme.palette.background.paper,
    };

    const tableData = hockeyGames
        .filter((game) => game.to_win !== "" || game.auto_over !== 0) // First, filter out games where `to_win` is empty
        .map((game) => {
            // Then, map over the filtered games to transform them
            const teamsData = JSON.parse(game.teams);
            const oddsData = JSON.parse(game.odds);

            return {
                ...game, // Spread other game properties if needed
                teamsData,
                oddsData,
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
                accessorKey: "name",
                header: "Game",
            },
            {
                accessorFn: (row) =>
                    row.auto_over === 1
                        ? "Over"
                        : row.to_win === "home"
                        ? `${row.teamsData.homeTeam.name} ML`
                        : `${row.teamsData.awayTeam.name} ML`,
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

export default HockeyTable;
