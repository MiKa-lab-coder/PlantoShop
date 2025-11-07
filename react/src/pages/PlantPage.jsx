import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { ShoppingCart, Undo2 } from 'lucide-react';

function PlantPage() {
    const { id } = useParams(); // Récupérer l'ID de la plante depuis l'URL
    const [plant, setPlant] = useState(null);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState(null);
    const navigate = useNavigate();

    useEffect(() => {
        const fetchPlant = async () => {
            try {
                // Récupérer la plante depuis le localStorage
                const selectedPlantJSON = localStorage.getItem('selectedPlant');
                if (selectedPlantJSON) {
                    const selectedPlant = JSON.parse(selectedPlantJSON);
                    // S'assurer que la plante stockée correspond à l'ID de l'URL
                    if (selectedPlant.id === Number(id)) {
                        setPlant(selectedPlant);
                        localStorage.removeItem('selectedPlant'); // Nettoyer après utilisation
                        setIsLoading(false);
                        return; // On a trouvé la plante, pas besoin de fetch
                    }
                }

                // Si la plante n'est pas dans le localStorage, on la fetch
                const response = await fetch(`http://localhost/api/plants/${id}`);
                if (!response.ok) {
                    throw new Error('Impossible de récupérer les détails de la plante.');
                }
                const data = await response.json();
                setPlant(data);

            } catch (err) {
                setError(err.message);
            } finally {
                setIsLoading(false);
            }
        };

        fetchPlant();
    }, [id]); // Le useEffect se redéclenche si l'ID dans l'URL change

    const handleAddToCart = (plantToAdd) => {
        const currentCart = JSON.parse(localStorage.getItem('cart') || '[]');
        const existingItemIndex = currentCart.findIndex(item => item.plant.id === plantToAdd.id);

        let newCart;
        if (existingItemIndex > -1) {
            newCart = currentCart.map((item, index) =>
                index === existingItemIndex ? { ...item, quantity: item.quantity + 1 } : item
            );
        } else {
            newCart = [...currentCart, { plant: plantToAdd, quantity: 1 }];
        }

        localStorage.setItem('cart', JSON.stringify(newCart));
        alert(`${plantToAdd.name} a été ajouté au panier !`);
    };

    // Fonction pour gérer le retour en arrière
    const handleGoBack = () => {
        navigate(-1); // Revient à la page précédente dans l'historique
    };

    // Affichage des erreurs
    if (isLoading) return <div className="text-center p-8">Chargement de la plante...</div>;
    if (error) return <div className="text-center p-8 text-red-600">Erreur: {error}</div>;
    if (!plant) return <div className="text-center p-8">Plante non trouvée.</div>;

    // Affichage de la plante
    return (
        <div className="container mx-auto p-8">
            {/* Bouton de retour en arrière */}
            <button 
                onClick={handleGoBack} 
                className="flex items-center gap-2 text-slate-600 hover:text-green-700 mb-6 px-4 py-2 rounded-md
                 border border-slate-300 hover:border-green-700 transition-colors duration-200"
            >
                <Undo2 size={20} /> Retour
            </button>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                {/* Colonne de l'image */}
                <div>
                    <img src={plant.imageUrl || 'https://via.placeholder.com/400'} alt={plant.name} className="w-full
                     h-auto object-cover rounded-lg shadow-lg" />
                </div>

                {/* Colonne des détails */}
                <div className="flex flex-col justify-center">
                    <h2 className="text-4xl font-bold text-green-700 mb-4">{plant.name}</h2>
                    <p className="text-slate-600 mb-6 text-lg">{plant.description}</p>
                    
                    <div className="flex items-center justify-between bg-gray-100 p-4 rounded-lg">
                        <span className="text-3xl font-bold text-green-700">{plant.price.toFixed(2)} €</span>
                        <button
                            onClick={() => handleAddToCart(plant)}
                            className="bg-green-700 text-white px-6 py-3 rounded-md hover:bg-green-600 flex
                             items-center gap-2 text-lg"
                        >
                            <ShoppingCart size={22} /> Ajouter au panier
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
}

export default PlantPage;