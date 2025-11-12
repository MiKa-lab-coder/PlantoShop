import {useState, useEffect} from 'react';
import {Trash2} from 'lucide-react';

function AdminOrdersPage() {
    const [orders, setOrders] = useState([]);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        const fetchOrders = async () => {
            try {
                const token = localStorage.getItem('token');
                if (!token) {
                    throw new Error('Vous devez être connecté pour voir les commandes.');
                }

                const response = await fetch('http://localhost/api/orders', {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                    },
                });
                if (!response.ok) {
                    throw new Error('Impossible de récupérer les commandes.');
                }
                const data = await response.json();
                setOrders(data);
                setIsLoading(false);
            } catch (err) {
                setError(err.message);
            }
        };
        fetchOrders();
    }, []);

    const handleDelete = async (orderId) => {
        if (!window.confirm(`Êtes-vous sûr de vouloir supprimer la commande #${orderId} ?`))
            return;
        try {
            const token = localStorage.getItem('token');
            if (!token) {
                throw new Error('Vous devez être connecté pour supprimer une commande.');
            }
            const response = await fetch(`http://localhost/api/orders/${orderId}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'La suppression a échoué.');
            }
            setOrders(prev => prev.filter(o => o.id !== orderId));
            alert('Commande supprimée.');
        } catch (err) {
            alert(err.message);
        }
    }

    if (isLoading) return <div className="text-center p-8">Chargement...</div>;
    if (error) return <div className="text-center p-8 text-red-600">Erreur: {error}</div>;

    return (
        <div className="p-4 md:p-8">
            <h2 className="text-3xl font-bold text-slate-800 mb-6">Gestion des Commandes</h2>
            <div className="bg-white rounded-lg shadow-md overflow-x-auto">
                <table className="w-full text-left">
                    <thead className="bg-gray-50 border-b">
                    <tr>
                        <th className="p-4 font-semibold">ID Commande</th>
                        <th className="p-4 font-semibold">ID Client</th>
                        <th className="p-4 font-semibold">Email Client</th>
                        <th className="p-4 font-semibold">Date</th>
                        <th className="p-4 font-semibold">Montant Total</th>
                        <th className="p-4 font-semibold text-center">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    {orders.map(order => ( // Renommer la variable de boucle pour éviter la confusion
                        <tr key={order.id} className="border-b hover:bg-gray-50">
                            <td className="p-4">{order.id}</td>
                            <td className="p-4">{order.client?.id || 'N/A'}</td>
                            <td className="p-4">{order.orderDetails?.clientEmail || 'N/A'}</td>
                            <td className="p-4">{new Date(order.orderDetails?.orderDate).toLocaleDateString()
                                || 'N/A'}</td>
                            <td className="p-4">{parseFloat(order.orderDetails?.totalPrice).toFixed(2)
                                || '0.00'} €
                            </td>
                            <td className="p-4">
                                <div className="flex justify-center gap-4">
                                    <button onClick={() => handleDelete(order.id)}
                                            className="text-red-600 hover:text-red-800"
                                            title="Supprimer"><Trash2 size={20}/>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
}

export default AdminOrdersPage;