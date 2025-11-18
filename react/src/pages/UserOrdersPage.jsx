import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { Eye } from 'lucide-react';

function UserOrdersPage() {
    const [orders, setOrders] = useState([]);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        const fetchOrders = async () => {
            try {
                const token = localStorage.getItem('token');
                if (!token) {
                    throw new Error('Vous devez être connecté pour voir vos commandes.');
                }

                const response = await fetch('http://localhost/api/user/orders', {
                    headers: { 'Authorization': `Bearer ${token}` },
                });

                if (!response.ok) {
                    throw new Error('Impossible de récupérer l\'historique des commandes.');
                }
                const data = await response.json();
                setOrders(data);
            } catch (err) {
                setError(err.message);
            } finally {
                setIsLoading(false);
            }
        };

        fetchOrders();
    }, []);

    if (isLoading) return <div className="text-center p-8">Chargement de vos commandes...</div>;
    if (error) return <div className="text-center p-8 text-red-600">Erreur: {error}</div>;

    return (
        <div>
            <h1 className="text-3xl font-bold text-slate-800 mb-6">Mes Commandes</h1>
            {orders.length === 0 ? (
                <p>Vous n'avez pas encore passé de commande.</p>
            ) : (
                <div className="bg-white rounded-lg shadow-md overflow-auto">
                    <table className="w-full text-left">
                        <thead className="bg-gray-50 border-b">
                            <tr>
                                <th className="p-4 font-semibold">ID Commande</th>
                                <th className="p-4 font-semibold">Date</th>
                                <th className="p-4 font-semibold">Montant Total</th>
                                <th className="p-4 font-semibold text-center">Détails</th>
                            </tr>
                        </thead>
                        <tbody>
                            {orders.map(order => (
                                <tr key={order.id} className="border-b hover:bg-gray-50">
                                    <td className="p-4 font-medium">#{order.id}</td>
                                    <td className="p-4">{new Date(order.orderDetails?.orderDate).toLocaleDateString()}</td>
                                    <td className="p-4">{parseFloat(order.orderDetails?.totalPrice).toFixed(2)} €</td>
                                    <td className="p-4 text-center">
                                        {/* On passe l'objet 'order' complet via le state du Link */}
                                        <Link 
                                            to={`/user/orders/${order.id}`} 
                                            state={{ order: order }}
                                            className="text-green-600 hover:text-green-800 inline-flex items-center justify-center"
                                            title="Voir les détails"
                                        >
                                            <Eye size={20} />
                                        </Link>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}
        </div>
    );
}

export default UserOrdersPage;
