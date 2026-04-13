import { useState } from "react";
import { useNavigate } from "react-router-dom";
import { Search } from 'lucide-react';
import { API_URL } from '../services/api.js';

function SearchBar() {

    // Etat initial du formulaire
    const [searchQuery, setSearchQuery] = useState('');
    const [error, setError] = useState(null);
    const [isLoading, setIsLoading] = useState(false);

    // Fonction de navigation
    const navigate = useNavigate();

    // Fonction de recherche
    const handleSubmit = async (event) => {
        event.preventDefault();
        if (!searchQuery.trim()) return;

        setIsLoading(true);
        setError(null);

        try {
            // Rechercher des plantes par leur nom
            const plantResponse = await fetch(`${API_URL}/api/plants/search/${searchQuery}`);
            const plantsData = await plantResponse.json();

            let finalResults = plantsData;

            // Si aucune plante n'est trouvée, rechercher par catégorie
            if (plantsData.length === 0) {
                const categoryResponse = await fetch(`${API_URL}/api/categories/search/${searchQuery}`);
                const categoriesData = await categoryResponse.json();

                // Simplification pour ne prendre que le premier résultat et éviter des mélanges de catégorie et de plante
                // Ex: la recherche plante donnerait : plante d'intérieur / extérieur / grasse
                if (categoriesData.length > 0) {
                    const firstCategoryId = categoriesData[0].id;
                    const plantsByCategoryResponse =
                        await fetch(`${API_URL}/api/plants/by-category/${firstCategoryId}`);
                    finalResults = await plantsByCategoryResponse.json();
                }
            }

            localStorage.setItem('searchResults', JSON.stringify({ data: finalResults, ts: Date.now() }));
            navigate('/shop');

        } catch (err) {
            setError(err.message);
            console.error('Erreur de recherche:', err);
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <div className="w-full max-w-md mx-auto">
            <form onSubmit={handleSubmit} className="relative">
                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <Search className="text-slate-400" size={20} />
                </div>
                {/* Champ de recherche */}
                <input
                    type="text"
                    value={searchQuery}
                    onChange={(e) => setSearchQuery(e.target.value)}
                    placeholder="Rechercher une plante ou une catégorie..."
                    className="w-full pl-10 p-2 border border-slate-300 rounded-md text-slate-800 focus:ring-2
                     focus:ring-green-500 focus:border-green-500"
                    disabled={isLoading}
                />
                {/* Bouton de recherche */}
                <button type="submit" disabled={isLoading} className="absolute inset-y-0 right-0 px-4 flex items-center
                 bg-green-700 text-white rounded-r-md hover:bg-green-600 disabled:bg-green-400">
                    {isLoading ? '...' : <Search size={20} />}
                </button>
            </form>
            {error && <p className="text-red-600 text-sm text-center mt-2">{error}</p>}
        </div>
    );
}

export default SearchBar;