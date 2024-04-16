import React from "react";
import Dropdown from "@/Components/Dropdown";

const ResultsDropdown = () => {
    return (
        <div>
            {" "}
            <Dropdown>
                <Dropdown.Trigger>
                    <span className="inline-flex rounded-md">
                        <button
                            type="button"
                            className="inline-flex items-center px-8 py-2 border border-transparent text-sm leading-4 font-semibold rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150 dark:bg-gray-800 dark:text-gray-100"
                        >
                            Results
                            <svg
                                className="ms-2 -me-0.5 h-4 w-4"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path
                                    fillRule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clipRule="evenodd"
                                />
                            </svg>
                        </button>
                    </span>
                </Dropdown.Trigger>

                <Dropdown.Content>
                    <Dropdown.Link href={route("results.daily")}>
                        Daily
                    </Dropdown.Link>
                    <Dropdown.Link href={route("results.monthly")}>
                        Monthly
                    </Dropdown.Link>
                </Dropdown.Content>
            </Dropdown>
        </div>
    );
};

export default ResultsDropdown;
