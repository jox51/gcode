import { Link, Head } from "@inertiajs/react";
import Hero from "@/Components/Hero";
import Features from "@/Components/Features";
import Pricing from "@/Components/Pricing";
import Footer from "@/Components/Footer";
import AppLayout from "@/Layouts/AppLayout";

export default function Welcome({ auth, laravelVersion, phpVersion }) {
    const handleImageError = () => {
        document
            .getElementById("screenshot-container")
            ?.classList.add("!hidden");
        document.getElementById("docs-card")?.classList.add("!row-span-1");
        document
            .getElementById("docs-card-content")
            ?.classList.add("!flex-row");
        document.getElementById("background")?.classList.add("!hidden");
    };

    return (
        <>
            <Head title="Welcome" />
            <Hero auth={auth} />

            <Features />

            <Pricing />

            {/* <Footer /> */}
        </>
    );
}
