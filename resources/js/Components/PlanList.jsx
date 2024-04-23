export default function PlanList({ planId, planName, planState, planType }) {
    return (
        <div>
            <div className="px-4 sm:px-0">
                <h3 className="text-base font-semibold  leading-7 text-gray-900 dark:text-gray-100">
                    Plan Information
                </h3>
                <p className="mt-1 max-w-2xl text-sm leading-6 text-gray-500">
                    Details
                </p>
            </div>
            <div className="mt-6 border-t border-gray-100">
                <dl className="divide-y divide-gray-100">
                    <div className="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                        <dt className="text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">
                            Plan Id
                        </dt>
                        <dd className="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-100 sm:col-span-2 sm:mt-0">
                            {planId}
                        </dd>
                    </div>
                    <div className="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                        <dt className="text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">
                            Plan Name
                        </dt>
                        <dd className="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-100 sm:col-span-2 sm:mt-0">
                            {planName}
                        </dd>
                    </div>
                    <div className="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                        <dt className="text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">
                            Plan State
                        </dt>
                        <dd className="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-100 sm:col-span-2 sm:mt-0">
                            {planState}
                        </dd>
                    </div>
                    <div className="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                        <dt className="text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">
                            Plan Type
                        </dt>
                        <dd className="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-100 sm:col-span-2 sm:mt-0">
                            {planType}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    );
}
