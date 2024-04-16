import {
    ArrowPathIcon,
    CloudArrowUpIcon,
    FingerPrintIcon,
    LockClosedIcon,
} from "@heroicons/react/24/outline";

const features = [
    {
        name: "Instant Picks Deployment",
        description:
            "Forget waiting. Get immediate access to picks that are as close to a sure thing as it gets. Time is money, friend, and we're not here to waste either.",
        icon: CloudArrowUpIcon,
    },
    {
        name: "Rock-Solid Reliability",
        description:
            "Our picks are backed by a proprietary numerology system that's as reliable as gravity. Doubt it? See for yourself. We’re not gambling on guesses; we're calculating wins.",
        icon: LockClosedIcon,
    },
    {
        name: "Effortless Integration",
        description:
            "Integrating our picks into your betting strategy is as easy as breathing. No complicated processes. Just straightforward winning advice, ready when you are.",
        icon: ArrowPathIcon,
    },
    {
        name: "Unbreakable Security",
        description:
            "Your trust is our top priority. We safeguard your information with the tenacity of a lion guarding its cubs. With us, your secrets (and picks) are safe.",
        icon: FingerPrintIcon,
    },
];

export default function Features() {
    return (
        <div className="bg-white py-24 sm:py-32 dark:bg-gray-900" id="features">
            <div className="mx-auto max-w-7xl px-6 lg:px-8">
                <div className="mx-auto max-w-2xl lg:text-center">
                    <h2 className="text-base font-semibold leading-7 text-orange-500 dark:text-orange-400">
                        A Game Changer in Sports Betting
                    </h2>
                    <p className="mt-2 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl dark:text-gray-100">
                        Unlock the Secret to Consistent Wins
                    </p>
                    <p className="mt-6 text-lg leading-8 text-gray-600 dark:text-gray-300">
                        Imagine having a crystal ball that reveals the most
                        lucrative sports picks. That's us. We've cracked the
                        code to turning your bets into wins. Ready to play the
                        winning game?
                    </p>
                </div>
                <div className="mx-auto mt-16 max-w-2xl sm:mt-20 lg:mt-24 lg:max-w-4xl">
                    <dl className="grid max-w-xl grid-cols-1 gap-x-8 gap-y-10 lg:max-w-none lg:grid-cols-2 lg:gap-y-16">
                        {features.map((feature) => (
                            <div key={feature.name} className="relative pl-16">
                                <dt className="text-base font-semibold leading-7 text-gray-900 dark:text-gray-100">
                                    <div className="absolute left-0 top-0 flex h-10 w-10 items-center justify-center rounded-lg bg-orange-500 dark:bg-orange-600">
                                        <feature.icon
                                            className="h-6 w-6 text-white"
                                            aria-hidden="true"
                                        />
                                    </div>
                                    {feature.name}
                                </dt>
                                <dd className="mt-2 text-base leading-7 text-gray-600 dark:text-gray-400">
                                    {feature.description}
                                </dd>
                            </div>
                        ))}
                    </dl>
                </div>
            </div>
        </div>
    );
}
