import {useState, useEffect} from 'react';
import {Star, MessageSquare} from 'lucide-react';

// Modal pour laisser un avis
const ReviewModal = ({plant, onClose, onSubmit}) => {
    const [rating, setRating] = useState(5);
    const [comment, setComment] = useState('');

    // Fonction de soumission du formulaire
    const handleSubmit = (e) => {
        e.preventDefault();
        onSubmit(plant.id, {rating, comment});
    };

    // Affichage de la modale
    return (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div className="bg-white p-8 rounded-lg shadow-xl w-full max-w-md">
                <h2 className="text-2xl font-bold mb-4">Laisser un avis pour {plant.name}</h2>
                <form onSubmit={handleSubmit}>
                    <div className="mb-4">
                        <label className="block mb-2">Note</label>
                        <select value={rating} onChange={(e) => setRating
                        (Number(e.target.value))} className="w-full p-2 border rounded">
                            {[5, 4, 3, 2, 1].map(r => <option key={r} value={r}>{r} étoile{r > 1 ? 's' : ''}</option>)}
                        </select>
                    </div>
                    <div className="mb-6">
                        <label className="block mb-2">Commentaire</label>
                        <textarea
                            value={comment}
                            onChange={(e) => setComment(e.target.value)}
                            className="w-full p-2 border rounded"
                            rows="4"
                            required
                        />
                    </div>
                    <div className="flex justify-end gap-4">
                        <button type="button" onClick={onClose} className="px-4 py-2 bg-gray-200 rounded">Annuler
                        </button>
                        <button type="submit" className="px-4 py-2 bg-green-600 text-white rounded">Envoyer</button>
                    </div>
                </form>
            </div>
        </div>
    );
};

// Page des commandes de l'utilisateur
function UserOrdersPage() {
    const [orders, setOrders] = useState([]);
    const [userReviews, setUserReviews] = useState([]);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState(null);
    const [selectedPlant, setSelectedPlant] = useState(null); // Pour la modale

    // Récupération des commandes et des avis de l'utilisateur
    useEffect(() => {
        const fetchData = async () => {
            try {
                const token = localStorage.getItem('token');
                if (!token) throw new Error('Vous devez être connecté.');

                // Récupérer les commandes et les avis en parallèle
                const [ordersResponse, reviewsResponse] = await Promise.all([
                    fetch('http://localhost/api/user/orders', {headers: {'Authorization': `Bearer ${token}`}}),
                    fetch('http://localhost/api/user/reviews', {headers: {'Authorization': `Bearer ${token}`}})
                ]);

                if (!ordersResponse.ok || !reviewsResponse.ok) {
                    throw new Error('Impossible de récupérer vos données.');
                }

                const ordersData = await ordersResponse.json();
                // Debug
                // console.log('Orders Data:', ordersData);

                const reviewsData = await reviewsResponse.json();
                // Debug
                // console.log('Reviews Data:', reviewsData);

                // Mettre à jour les états
                setOrders(ordersData);
                setUserReviews(reviewsData);
            } catch (err) {
                setError(err.message);
            } finally {
                setIsLoading(false);
            }
        };

        fetchData();
    }, []);

    // Fonction de soumission du formulaire d'avis
    const handleReviewSubmit = async (plantId, reviewData) => {
        try {
            const token = localStorage.getItem('token');
            const response = await fetch(`http://localhost/api/plants/${plantId}/reviews`, {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'Authorization': `Bearer ${token}`},
                body: JSON.stringify(reviewData),
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.error || 'Impossible de soumettre l\'avis.');
            }

            const newReview = await response.json();
            // Mettre à jour la liste des avis pour désactiver le bouton
            setUserReviews(prevReviews => [...prevReviews, newReview]);
            setSelectedPlant(null); // Fermer la modale
            alert('Avis envoyé avec succès !');

        } catch (err) {
            alert(err.message);
        }
    };

    // Fonction pour vérifier si l'utilisateur a déjà laissé un avis pour cette plante
    const hasUserReviewed = (plantId) => {
        return userReviews.some(review => review.plantId === plantId);
    };

    if (isLoading) return <div className="text-center p-8">Chargement de vos commandes...</div>;
    if (error) return <div className="text-center p-8 text-red-600">Erreur: {error}</div>;

    return (
        <div>
            <h1 className="text-3xl font-bold text-slate-800 mb-6">Mes Commandes</h1>
            {orders.length === 0 ? (
                <p>Vous n'avez pas encore passé de commande.</p>
            ) : (
                <div className="space-y-6">
                    {orders.map(order => (
                        <div key={order.id} className="bg-white p-6 rounded-lg shadow-md">
                            <div className="flex justify-between items-center mb-4">
                                <h2 className="text-xl font-semibold text-green-700">Commande #{order.id}</h2>
                                <span className="text-sm text-slate-500">
                                    Passée le: {new Date(order.orderDetails.orderDate).toLocaleDateString()}
                                </span>
                            </div>
                            <div className="space-y-4">
                                {order.plants.map(plant => (
                                    <div key={plant.id} className="flex items-center justify-between gap-4 py-2
                                     border-b last:border-b-0">
                                        <div className="flex items-center gap-4">
                                            <img src={plant.imageUrl} alt={plant.name} className="w-12 h-12
                                             object-cover rounded"/>
                                            <p className="font-medium text-slate-700">{plant.name}</p>
                                        </div>
                                        {!hasUserReviewed(plant.id) && (
                                            <button
                                                onClick={() => setSelectedPlant(plant)}
                                                className="text-sm flex items-center gap-1 text-green-600 hover:underline"
                                            >
                                                <MessageSquare size={16}/> Laisser un avis
                                            </button>
                                        )}
                                    </div>
                                ))}
                            </div>
                            <div className="text-right mt-4">
                                <p className="text-lg font-bold text-slate-800">
                                    Total: {parseFloat(order.orderDetails.totalPrice).toFixed(2)} €
                                </p>
                            </div>
                        </div>
                    ))}
                </div>
            )}
            {selectedPlant && (
                <ReviewModal
                    plant={selectedPlant}
                    onClose={() => setSelectedPlant(null)}
                    onSubmit={handleReviewSubmit}
                />
            )}
        </div>
    );
}

export default UserOrdersPage;
