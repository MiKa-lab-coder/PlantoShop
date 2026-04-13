import { useState, useEffect } from 'react';
import { useParams, useLocation, useNavigate } from 'react-router-dom';
import { Undo2, MessageSquare, Star } from 'lucide-react';
import { API_URL } from '../services/api.js';

// Modal pour laisser un avis
const ReviewModal = ({ plantName, plantId, onClose, onSubmit }) => {
    const [rating, setRating] = useState(5);
    const [comment, setComment] = useState('');

    // Fonction pour soumettre l'avis
    const handleSubmit = (e) => {
        e.preventDefault();
        // Verification des données avant de les envoyer
        if (typeof plantId !== 'number') {
            alert('Erreur : ID de plante invalide.');
            return;
        }
        onSubmit(plantId, { rating, comment });
    };

    return (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div className="bg-white p-8 rounded-lg shadow-xl w-full max-w-md">
                <h2 className="text-2xl font-bold mb-4">Laisser un avis pour {plantName}</h2>
                <form onSubmit={handleSubmit}>
                    <div className="mb-4">
                        <label className="block mb-2">Note</label>
                        <select value={rating} onChange={(e) =>
                            setRating(Number(e.target.value))} className="w-full p-2 border rounded">
                            {[5, 4, 3, 2, 1].map(r => <option key={r} value={r}>{r} étoile{r > 1 ? 's' : ''}</option>)}
                        </select>
                    </div>
                    <div className="mb-6">
                        <label className="block mb-2">Commentaire</label>
                        <textarea value={comment} onChange={(e) =>
                            setComment(e.target.value)} className="w-full p-2 border rounded" rows="4" required minLength="5" />
                    </div>
                    <div className="flex justify-end gap-4">
                        <button type="button" onClick={onClose} className="px-4 py-2 bg-gray-200 rounded">Annuler</button>
                        <button type="submit" className="px-4 py-2 bg-green-700 text-white rounded">Envoyer</button>
                    </div>
                </form>
            </div>
        </div>
    );
};

// Page d'historique des commandes
function UserOrdersDetailsPage() {
    const { id } = useParams();
    const location = useLocation();
    const navigate = useNavigate();

    const [order, setOrder] = useState(location.state?.order || null);
    const [userReviews, setUserReviews] = useState([]);
    const [isLoading, setIsLoading] = useState(!order); 
    const [error, setError] = useState(null);
    const [selectedPlant, setSelectedPlant] = useState(null);

    useEffect(() => {
        const token = localStorage.getItem('token');
        if (!token) {
            setError('Vous devez être connecté.');
            setIsLoading(false);
            return;
        }

        const fetchInitialData = async () => {
            try {
                const reviewsResponse = await fetch(`${API_URL}/api/user/reviews`,
                    { headers: { 'Authorization': `Bearer ${token}` } });
                if (!reviewsResponse.ok) throw new Error('Impossible de récupérer vos avis.');
                const reviewsData = await reviewsResponse.json();
                setUserReviews(reviewsData);

                if (!order) {
                    const orderResponse = await fetch(`${API_URL}/api/orders/${id}`,
                        { headers: { 'Authorization': `Bearer ${token}` } });
                    if (!orderResponse.ok) throw new Error('Impossible de récupérer les détails de la commande.');
                    const orderData = await orderResponse.json();
                    setOrder(orderData);
                }
            } catch (err) {
                setError(err.message);
            } finally {
                setIsLoading(false);
            }
        };

        fetchInitialData();
    }, [id, order]);

    // Fonction pour soumettre un avis
    const handleReviewSubmit = async (plantId, reviewData) => {
        try {
            const token = localStorage.getItem('token');
            const response = await fetch(`${API_URL}/api/plants/${plantId}/reviews`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
                body: JSON.stringify(reviewData),
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.error || 'Impossible de soumettre l\'avis.');
            }

            const newReview = await response.json();
            setUserReviews(prev => [...prev, newReview]);
            setSelectedPlant(null);
            alert('Avis envoyé avec succès !');
        } catch (err) {
            alert(err.message);
        }
    };

    // Vérification si l'utilisateur a déjà laissé un avis pour cette plante
    const hasUserReviewed = (plantId) => userReviews.some(review => review.plantId === plantId);

    if (isLoading) return <div className="text-center p-8">Chargement...</div>;
    if (error) return <div className="text-center p-8 text-red-600">{error}</div>;
    if (!order || !order.orderDetails || !order.orderDetails.plantSummary) {
        return <div className="text-center p-8">Détails de la commande non trouvés.</div>;
    }

    return (
        <div>
            <button onClick={() => navigate(-1)} className="flex items-center gap-2 text-slate-600
             hover:text-green-700 mb-6">
                <Undo2 size={20} /> Retour aux commandes
            </button>

            <div className="bg-white p-6 rounded-lg shadow-md">
                <div className="flex justify-between items-center mb-4 border-b pb-4">
                    <div>
                        <h1 className="text-2xl font-bold text-slate-800">Détails de la Commande #{order.id}</h1>
                        <p className="text-sm text-slate-500">
                            Passée le: {new Date(order.orderDetails.orderDate).toLocaleDateString()}
                        </p>
                    </div>
                    <p className="text-xl font-bold text-slate-800">
                        Total: {parseFloat(order.orderDetails.totalPrice).toFixed(2)} €
                    </p>
                </div>
                
                <h2 className="text-xl font-semibold text-green-700 mb-4">Articles commandés</h2>
                <div className="space-y-4">
                    {order.orderDetails.plantSummary.map((item, index) => (
                        <div key={index} className="flex items-center justify-between gap-4 p-4 border rounded-lg">
                            <div>
                                <p className="font-semibold text-lg text-slate-700">{item.name}</p>
                                <p className="text-sm text-slate-500">
                                    Prix unitaire: {item.price.toFixed(2)} €
                                </p>
                            </div>
                            <div className="flex items-center gap-4">
                                <p className="font-semibold text-slate-700">Quantité: {item.quantity}</p>
                                {!hasUserReviewed(item.id) ? (
                                    <button onClick={() => setSelectedPlant(item)} className="text-sm flex
                                     items-center gap-1 text-green-700 hover:underline">
                                        <MessageSquare size={16} /> Laisser un avis
                                    </button>
                                ) : (
                                    <div className="text-sm flex items-center gap-1 text-gray-400">
                                        <Star className="text-yellow-500" size={16} /> Avis laissé
                                    </div>
                                )}
                            </div>
                        </div>
                    ))}
                </div>
            </div>

            {selectedPlant && (
                <ReviewModal 
                    plantName={selectedPlant.name} 
                    plantId={selectedPlant.id}
                    onClose={() => setSelectedPlant(null)} 
                    onSubmit={handleReviewSubmit}
                />
            )}
        </div>
    );
}

export default UserOrdersDetailsPage;
