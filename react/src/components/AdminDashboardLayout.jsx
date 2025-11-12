import { NavLink, Outlet } from 'react-router-dom';
import { Sprout, ShoppingBag, CircleUserRound } from 'lucide-react';

function AdminDashboardLayout() {
    // Fonction pour styliser les liens actifs
    const getLinkClasses = ({ isActive }) => {
        const baseClasses = "flex items-center gap-3 p-3 rounded-lg transition-colors duration-200";
        if (isActive) {
            return `${baseClasses} bg-green-700 text-white font-semibold`;
        }
        return `${baseClasses} hover:bg-green-100 text-slate-700`;
    };

    return (
        <div className="flex flex-col md:flex-row min-h-[calc(100vh-160px)]">
            {/* Sidebar de navigation */}
            <aside className="w-full md:w-64 bg-white p-4 shadow-md md:shadow-none md:border-r">
                <h2 className="text-2xl font-bold text-green-700 mb-8">Administration</h2>
                <nav className="flex flex-col gap-2">
                    <NavLink to="/admin/plants" className={getLinkClasses}>
                        <Sprout size={20} /> Plantes
                    </NavLink>
                    <NavLink to="/admin/orders" className={getLinkClasses}>
                        <ShoppingBag size={20} /> Commandes
                    </NavLink>
                    <NavLink to="/admin/users" className={getLinkClasses}>
                        <CircleUserRound size={20} /> Comptes
                    </NavLink>
                </nav>
            </aside>

            {/* Contenu principal de la section */}
            <main className="flex-grow p-4 md:p-8 bg-gray-50">
                <Outlet />
            </main>
        </div>
    );
}

export default AdminDashboardLayout;