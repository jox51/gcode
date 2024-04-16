import { useState } from "react";
import { Dialog } from "@headlessui/react";
import { Link } from "@inertiajs/react";
import { Bars3Icon, XMarkIcon } from "@heroicons/react/24/outline";
import Logo from "@/assets/logo.png"; // Ensure this path is correct

const navigation = [
    { name: "Picks", href: "/picks" },
    { name: "Features", href: "#" },
    { name: "Results", href: "#" },
    { name: "About Me", href: "#" },
];

export default function Hero({ auth }) {
    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

    return (
        <div className="bg-white dark:bg-gray-800">
            <div className="relative isolate px-6 pt-14 lg:px-8 ">
                <div
                    className="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80"
                    aria-hidden="true"
                >
                    <div
                        className="relative left-[calc(50%-11rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 rotate-[30deg] "
                        style={{
                            background:
                                "linear-gradient(to top right, #FF7E5F, #FEB47B)", // Adjusted for green gradient
                            opacity: 0.6,
                            clipPath:
                                "polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)",
                        }}
                    />
                </div>
                <div className="mx-auto max-w-2xl py-32 sm:py-48 lg:py-56">
                    <div className="text-center">
                        <h1 className="text-4xl font-bold tracking-tight text-gray-900  dark:text-gray-100 sm:text-6xl">
                            Discover Hidden Profits with Numerology Sports Picks
                        </h1>
                        <p className="mt-6 text-lg leading-8 text-gray-600  dark:text-gray-300">
                            Unlock the power of our proprietary numerology code
                            to reveal the most profitable sports picks. Where
                            numbers meet sports, fortunes are made.
                        </p>
                        <div className="mt-10 flex items-center justify-center gap-x-6">
                            <a
                                href="#"
                                className="rounded-md bg-orange-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-orange-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-600"
                            >
                                Get started
                            </a>
                            <a
                                href="#"
                                className="text-sm font-semibold leading-6 text-gray-900 dark:text-gray-100"
                            >
                                Learn more <span aria-hidden="true">→</span>
                            </a>
                        </div>
                    </div>
                </div>
                <div
                    className="absolute inset-x-0 top-[calc(100%-13rem)] -z-10 transform-gpu overflow-hidden blur-3xl sm:top-[calc(100%-30rem)]"
                    aria-hidden="true"
                >
                    <div
                        className="relative left-[calc(50%+3rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2"
                        style={{
                            background:
                                "linear-gradient(to bottom left, #FF9966, #FF5E62)", // Another green gradient
                            opacity: 0.6,
                            clipPath:
                                "polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)",
                        }}
                    />
                </div>
            </div>
        </div>
    );
}
