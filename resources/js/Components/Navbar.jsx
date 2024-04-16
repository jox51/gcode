import { useState, useEffect } from "react";
import { Dialog } from "@headlessui/react";
import { usePage } from "@inertiajs/react";
import { Link } from "@inertiajs/react";
import {
    Bars3Icon,
    XMarkIcon,
    MoonIcon,
    SunIcon,
} from "@heroicons/react/24/outline";
import Dropdown from "@/Components/Dropdown";
import Logo from "@/assets/logo.png";
import ResultsDropdown from "./ResultsDropdown";
import { useThemeStore } from "@/store/themeStore"; // Adjust the import path

export default function Navbar() {
    // const [darkMode, setDarkMode] = useState(false);
    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
    const { auth } = usePage().props;
    const navigation = getNavigation(!!auth.user);
    // Zustand store for managing dark mode
    const { darkMode, toggleDarkMode } = useThemeStore();
    // Effect to toggle dark class on html element based on Zustand store's state
    useEffect(() => {
        document.documentElement.classList.toggle("dark", darkMode);
    }, [darkMode]);

    useEffect(() => {
        localStorage.setItem("darkMode", darkMode);
    }, [darkMode]);

    function getNavigation(authenticated) {
        if (authenticated) {
            return [
                // { name: "NBA", href: "/nba" },
                // Any other links you want to show to logged-in users
                // { name: "Soccer", href: "/soccer" },
                { name: "Hockey", href: "/hockey" },
                { name: "Baseball", href: "/baseball" },
                { name: "Tennis", href: "/tennis" },
            ];
        } else {
            return [
                { name: "Baseball", href: "/baseball" },
                { name: "Hockey", href: "/hockey" },
                { name: "Tennis", href: "/tennis" },
                { name: "Features", href: "#features" },
                { name: "About Me", href: "#" },
            ];
        }
    }

    return (
        <header className="absolute inset-x-0 top-0 z-50 dark:bg-gray-800">
            <nav
                className="flex items-center justify-between p-2 lg:px-8"
                aria-label="Global"
            >
                <div className="flex lg:flex-1">
                    <Link href="/" className="-m-1.5 p-1.5">
                        <span className="sr-only">Your Company</span>
                        <img className="h-24 w-auto" src={Logo} alt="" />
                    </Link>
                </div>
                <div className="flex lg:hidden">
                    <button
                        type="button"
                        className="-m-2.5 inline-flex items-center justify-center rounded-md p-2.5 text-gray-700"
                        onClick={() => setMobileMenuOpen(true)}
                    >
                        <span className="sr-only">Open main menu</span>
                        <Bars3Icon
                            className="h-6 w-6 dark:text-gray-300"
                            aria-hidden="true"
                        />
                    </button>
                </div>
                <div className="hidden lg:flex lg:gap-x-12 ">
                    {navigation.map((item) => (
                        <Link
                            key={item.name}
                            href={item.href}
                            className="text-sm font-semibold leading-6  text-gray-900 dark:text-gray-100"
                            onClick={() => setMobileMenuOpen(false)}
                        >
                            {item.name}
                        </Link>
                    ))}
                </div>
                <ResultsDropdown />

                <div className="hidden pr-8 lg:flex lg:flex-1 lg:justify-end ">
                    {auth.user ? (
                        <>
                            {/* <Link
                                href={route("dashboard")}
                                className="rounded-md  text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none  "
                            >
                                Dashboard
                            </Link> */}
                            <div className="hidden sm:flex sm:items-center sm:ms-6  ">
                                <div className="ms-3 relative  ">
                                    <Dropdown>
                                        <Dropdown.Trigger>
                                            <span className="inline-flex rounded-md">
                                                <button
                                                    type="button"
                                                    className="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150 dark:bg-gray-800 dark:text-gray-100"
                                                >
                                                    {auth.user.name}

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
                                            <Dropdown.Link
                                                href={route("profile.edit")}
                                            >
                                                Profile
                                            </Dropdown.Link>
                                            <Dropdown.Link
                                                href={route("logout")}
                                                method="post"
                                                as="button"
                                            >
                                                Log Out
                                            </Dropdown.Link>
                                        </Dropdown.Content>
                                    </Dropdown>
                                </div>
                            </div>
                        </>
                    ) : (
                        <>
                            <Link
                                href={route("login")}
                                className="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:bg-gray-800 dark:text-gray-100"
                            >
                                Log in
                            </Link>
                            <Link
                                href={route("register")}
                                className="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:bg-gray-800 dark:text-gray-100"
                            >
                                Register
                            </Link>
                        </>
                    )}
                </div>
                {/* Dark Mode Toggle */}
                <button
                    onClick={toggleDarkMode}
                    className="p-2 rounded-md focus:outline-none focus:ring focus:ring-gray-300 dark:focus:ring-gray-700"
                >
                    {darkMode ? (
                        <SunIcon className="h-6 w-6 text-gray-800 dark:text-gray-200" />
                    ) : (
                        <MoonIcon className="h-6 w-6 text-gray-800 dark:text-gray-200" />
                    )}
                </button>
            </nav>
            <Dialog
                as="div"
                className="lg:hidden"
                open={mobileMenuOpen}
                onClose={setMobileMenuOpen}
            >
                <div className="fixed inset-0 z-50" />
                <Dialog.Panel className="fixed inset-y-0 right-0 z-50 w-full overflow-y-auto bg-white dark:bg-gray-700 px-6 py-6 sm:max-w-sm sm:ring-1 sm:ring-gray-900/10">
                    <div className="flex items-center justify-between">
                        <a href="#" className="-m-1.5 p-1.5">
                            <span className="sr-only">G-Algo</span>
                            <img className="h-16 w-auto" src={Logo} alt="" />
                        </a>
                        <button
                            type="button"
                            className="-m-2.5 rounded-md p-2.5 text-gray-700"
                            onClick={() => setMobileMenuOpen(false)}
                        >
                            <span className="sr-only">Close menu</span>
                            <XMarkIcon className="h-6 w-6" aria-hidden="true" />
                        </button>
                    </div>
                    <div className="mt-6 flow-root">
                        <div className="-my-6 divide-y divide-gray-500/10">
                            <div className="space-y-2 py-6">
                                {navigation.map((item) => (
                                    <Link
                                        key={item.name}
                                        href={item.href}
                                        className="-mx-3 block rounded-lg px-3 py-2 text-base dark:text-gray-300 font-semibold leading-7 text-gray-900 hover:bg-gray-50"
                                        onClick={() => setMobileMenuOpen(false)}
                                    >
                                        {item.name}
                                    </Link>
                                ))}
                            </div>
                            <div className="py-6">
                                {auth.user ? (
                                    <Link
                                        href={route("dashboard")}
                                        className="rounded-md px-3 py-2 text-black dark:text-gray-300 ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20]"
                                    >
                                        Dashboard
                                    </Link>
                                ) : (
                                    <>
                                        <Link
                                            href={route("login")}
                                            className="rounded-md px-3 py-2 text-black dark:text-gray-300 ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20]"
                                        >
                                            Log in
                                        </Link>
                                        <Link
                                            href={route("register")}
                                            className="rounded-md px-3 py-2 text-black dark:text-gray-300 ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20]"
                                        >
                                            Register
                                        </Link>
                                    </>
                                )}
                            </div>
                        </div>
                    </div>
                </Dialog.Panel>
            </Dialog>
        </header>
    );
}
