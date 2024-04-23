import { Link } from "@inertiajs/react";
import { usePage } from "@inertiajs/react";
import PlanList from "./PlanList";

function classNames(...classes) {
    return classes.filter(Boolean).join(" ");
}

export default function AdminComponent() {
    const { plans } = usePage().props;

    return (
        <div className="lg:flex lg:items-center lg:justify-content">
            <div className="min-w-0 flex-1">
                <h2 className="text-2xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:truncate sm:text-3xl sm:tracking-tight p-6">
                    Admin Page
                </h2>
                <div className="bg-gray-300 mx-auto pb-3 dark:bg-gray-500 sm:rounded-lg max-w-md">
                    <div className="px-4 py-5 sm:p-6">
                        <h3 className="text-base font-semibold leading-6 text-gray-900 dark:text-gray-100">
                            Plan id
                        </h3>
                        <div className="mt-2 max-w-xl text-sm text-gray-500 dark:text-gray-100">
                            <p>
                                Lorem ipsum dolor sit amet consectetur
                                adipisicing elit. Voluptatibus praesentium
                                tenetur pariatur.
                            </p>
                        </div>
                        <div className="mt-5">
                            <Link
                                href="create_plans"
                                as={"button"}
                                method="POST"
                                className="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 mx-6"
                            >
                                Create Plan
                            </Link>
                            <Link
                                href="show_plans"
                                as={"button"}
                                method="GET"
                                className="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                            >
                                Show Plans
                            </Link>
                        </div>
                    </div>
                </div>
                <div>
                    <ul className="flex justify-center items-center px-2 mx-auto w-2xl flex-wrap">
                        {plans &&
                            plans.plans.map((plan) => (
                                <li className="bg-gray-300 p-3 dark:bg-gray-500 max-w-lg sm:rounded-lg mt-6 mx-4 ">
                                    <PlanList
                                        key={plan.id}
                                        planId={plan.id}
                                        planName={plan.name}
                                        planState={plan.state}
                                        planType={plan.type}
                                    />
                                </li>
                            ))}
                    </ul>
                </div>
            </div>
        </div>
    );
}
