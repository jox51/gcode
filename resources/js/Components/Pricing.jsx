import { CheckIcon } from "@heroicons/react/20/solid";
import { Link } from "@inertiajs/react";

const includedFeatures = [
    "Exclusive access to our private picks app",
    "Private stats and results",
    "Daily picks notifications",
    "Your very own official winners' circle t-shirt",
];

export default function Pricing() {
    return (
        <div className="bg-white py-24 sm:py-32 dark:bg-gray-900">
            <div className="mx-auto max-w-7xl px-6 lg:px-8">
                <div className="mx-auto max-w-2xl sm:text-center">
                    <h2 className="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl dark:text-gray-100">
                        The Only Investment That Pays You To Join
                    </h2>
                    <p className="mt-6 text-lg leading-8 text-gray-600 dark:text-gray-400">
                        Imagine stepping into a world where your sports picks
                        aren't just guesses—they're calculated victories. That's
                        not just a dream; it's your new reality with our
                        lifetime membership.
                    </p>
                </div>
                <div className="mx-auto mt-16 max-w-2xl rounded-3xl ring-1 ring-gray-200 sm:mt-20 lg:mx-0 lg:flex lg:max-w-none dark:ring-gray-700/80">
                    <div className="p-8 sm:p-10 lg:flex-auto">
                        <h3 className="text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-100">
                            Lifetime Membership: Your Key to the Winners' Circle
                        </h3>
                        <p className="mt-6 text-base leading-7 text-gray-600 dark:text-gray-400">
                            With our proprietary numerology sports code, every
                            pick is a step closer to victory. It's time to turn
                            the game in your favor. Why gamble on luck when you
                            can bet on certainty?
                        </p>
                        <div className="mt-10 flex items-center gap-x-4">
                            <h4 className="flex-none text-sm font-semibold leading-6 text-orange-500 dark:text-orange-400">
                                Your Winners' Toolkit Includes
                            </h4>
                            <div className="h-px flex-auto bg-gray-100 dark:bg-gray-700" />
                        </div>
                        <ul
                            role="list"
                            className="mt-8 grid grid-cols-1 gap-4 text-sm leading-6 text-gray-600 dark:text-gray-400 sm:grid-cols-2 sm:gap-6"
                        >
                            {includedFeatures.map((feature) => (
                                <li key={feature} className="flex gap-x-3">
                                    <CheckIcon
                                        className="h-6 w-5 flex-none text-orange-500 dark:text-orange-400"
                                        aria-hidden="true"
                                    />
                                    {feature}
                                </li>
                            ))}
                        </ul>
                    </div>
                    <div className="-mt-2 p-2 lg:mt-0 lg:w-full lg:max-w-md lg:flex-shrink-0">
                        <div className="rounded-2xl bg-gray-50 py-10 text-center ring-1 ring-inset ring-gray-900/5 dark:bg-gray-800 dark:ring-gray-700/80 lg:flex lg:flex-col lg:justify-center lg:py-16">
                            <div className="mx-auto max-w-xs px-8">
                                <p className="text-base font-semibold text-gray-600 dark:text-gray-400">
                                    Monthly Payment, Lifetime Victory
                                </p>
                                <p className="mt-6 flex items-baseline justify-center gap-x-2">
                                    <span className="text-5xl font-bold tracking-tight text-gray-900 dark:text-gray-100">
                                        $8k
                                    </span>
                                    <span className="text-sm font-semibold leading-6 tracking-wide text-gray-600 dark:text-gray-400">
                                        USD
                                    </span>
                                </p>
                                <Link
                                    href="/subscribe"
                                    as={"button"}
                                    method="POST"
                                    className="mt-10 block w-full rounded-md bg-orange-500 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-orange-400 focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-orange-500 dark:hover:bg-orange-600"
                                    // data={{ id: planId }}
                                    disabled={true}
                                >
                                    Join the Winners' Circle
                                </Link>
                                <p className="mt-6 text-xs leading-5 text-gray-600 dark:text-gray-400">
                                    Secure your spot among the elite
                                    handicappers.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
