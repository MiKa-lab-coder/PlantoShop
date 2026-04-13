import { useState, useEffect } from 'react';
import { Star } from 'lucide-react';
import { API_URL } from '../services/api.js';

function ReviewCarousel({ plantId }) {
    const [reviews, setReviews] = useState([]);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        if (!plantId) return;

        const fetchReviews = async () => {
            try {
                const response = await fetch(`${API_URL}/api/plants/${plantId}/reviews`);
                if (!response.ok) {
                    throw new Error('Impossible de charger les avis.');
                }
                const data = await response.json();
                setReviews(data);
            } catch (err) {
                setError(err.message);
            } finally {
                setIsLoading(false);
            }
        };

        fetchReviews();
    }, [plantId]);

    // Si le chargement est en cours, ou s'il y a une erreur, ou s'il n'y a pas d'avis, on n'affiche rien.
    if (isLoading || error || reviews.length === 0) {
        return null;
    }

    return (
        <div className="mt-12">
            <h3 className="text-2xl font-bold text-slate-800 mb-6 text-center">Avis de nos clients</h3>
            <div className="relative">
                {/* Pour un vrai carrousel, on utiliserait une librairie comme 'react-slick' ou 'swiper' */}
                {/* Pour la simplicité, on affiche les avis dans une grille scrollable horizontalement. */}
                <div className="flex gap-6 overflow-x-auto pb-4">
                    {reviews.map(review => (
                        <div key={review.id} className="bg-white p-6 rounded-lg shadow-md flex-shrink-0 w-80">
                            <div className="flex items-center mb-4">
                                <div className="flex">
                                    {[...Array(5)].map((_, i) => (
                                        <Star 
                                            key={i} 
                                            size={20} 
                                            className={i < review.rating ? 'text-yellow-400 fill-current' : 'text-gray-300'} 
                                        />
                                    ))}
                                </div>
                                <p className="ml-auto text-sm text-slate-500">{new Date(review.createdAt).toLocaleDateString()}</p>
                            </div>
                            <p className="text-slate-600 mb-4">"{review.comment}"</p>
                            <p className="font-semibold text-right text-slate-700">- {review.username}</p>
                        </div>
                    ))}
                </div>
            </div>
        </div>
    );
}

export default ReviewCarousel;
