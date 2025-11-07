import { useState, useEffect } from 'react';
import { useOutletContext, Link } from 'react-router-dom';
import { ShoppingCart } from 'lucide-react';

function ShopPage() {
    const { isLoggedIn } = useOutletContext();
    const [plants, setPlants] = useState([]);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState(null);
    const [isSearchResult, setIsSearchResult] = useState(false);

    useEffect(() => {
        const fetchPlants = async () => {
            try {
                const searchResultsJSON = localStorage.getItem('searchResults');

                // On vérifie si la clé 'searchResults' existe, même si elle est vide.
                if (searchResultsJSON !== null) {
                    const searchResults = JSON.parse(searchResultsJSON);
                    setPlants(searchResults);
                    setIsSearchResult(true); // On note que c'est un résultat de recherche
                    localStorage.removeItem('searchResults'); // On nettoie après utilisation
                } else {
                    // Si la clé n'existe pas, on charge toutes les plantes.
                    const response = await fetch('http://localhost/api/plants');
                    if (!response.ok) {
                        throw new Error('Impossible de récupérer les plantes.');
                    }
                    const data = await response.json();
                    // Debug
                    // console.log(data)

                    setPlants(data);
                    setIsSearchResult(false);
                }
            } catch (err) {
                setError(err.message);
            } finally {
                setIsLoading(false);
            }
        };

        fetchPlants();
    }, []);

    // Fonction pour ajouter une plante au panier
    const handleAddToCart = (plantToAdd) => {
        const currentCart = JSON.parse(localStorage.getItem('cart') || '[]');
        const existingItemIndex = currentCart.findIndex(item => item.plant.id === plantToAdd.id);

        // Si la plante existe déjà dans le panier, on incrémente sa quantité
        let newCart;
        if (existingItemIndex > -1) {
            newCart = currentCart.map((item, index) =>
                index === existingItemIndex ? { ...item, quantity: item.quantity + 1 } : item
            );
        } else {
            newCart = [...currentCart, { plant: plantToAdd, quantity: 1 }];
        }

        // On met à jour le panier dans le localStorage
        localStorage.setItem('cart', JSON.stringify(newCart));
        alert(`${plantToAdd.name} a été ajouté au panier !`);
    };

    // Affichage des plantes
    if (isLoading) return <div className="text-center p-8">Chargement des plantes...</div>;
    if (error) return <div className="text-center p-8 text-red-600">Erreur: {error}</div>;

    return (
        <div className="container mx-auto p-4">
            <h2 className="text-3xl font-bold text-green-700 mb-8 text-center">
                {isSearchResult ? 'Résultats de votre recherche' : 'Notre Sélection de Plantes'}
            </h2>

            {plants.length > 0 ? (
                <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    {plants.map(plant => (
                        <div key={plant.id} className="bg-white rounded-lg shadow-md overflow-hidden">
                            <img src={plant.imageUrl || 'https://via.placeholder.com/200'} alt={plant.name}
                                 className="w-full h-48 object-cover" />
                            <div className="p-4">
                                <h3 className="text-xl font-semibold text-slate-800 mb-2">{plant.name}</h3>
                                <p className="text-slate-600 text-sm mb-4">{plant.description.substring(0, 70)}...</p>
                                <div className="flex justify-between items-center">
                                    <span className="text-lg font-bold text-green-700">{plant.price.toFixed(2)} €</span>
                                    <button
                                        onClick={() => handleAddToCart(plant)}
                                        className="bg-green-700 text-white px-4 py-2 rounded-md hover:bg-green-600
                                        flex items-center gap-2"
                                    >
                                        <ShoppingCart size={18} /> Ajouter
                                    </button>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            ) : (
                <div className="text-center p-8">
                    <p className="text-xl text-slate-700">Aucune plante ne correspond à vos critères.</p>
                    <Link to="/shop" className="mt-4 inline-block px-6 py-2 bg-green-700 text-white rounded hover:bg-green-600">
                        Voir toutes nos plantes
                    </Link>
                </div>
            )}
        </div>
    );
}

export default ShopPage;