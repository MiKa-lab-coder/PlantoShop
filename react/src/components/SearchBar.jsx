import {useState} from "react";
import {useNavigate} from "react-router-dom";
import {Search} from 'lucide-react';

function SearchBar () {
    // État initial
    const [searchQuery, setSearchQuery] = useState('');
    const [error, setError] = useState(null);
    const [isLoading, setIsLoading] = useState(false);

    // Fonction de navigation
    const navigate = useNavigate();

    // Fonction de soumission de la recherche
    const handleSubmit = async (event) => {
        event.preventDefault();
        if (!searchQuery.trim()) return;

        setIsLoading(true);
        setError(null);

        try {
            // On lance les deux recherches en parallèle
            const [plantResponse, categoryResponse] = await Promise.all([
                fetch(`http://localhost/api/plants/search/${searchQuery}`),
                fetch(`http://localhost/api/categories/search/${searchQuery}`)
            ]);

            const plantData = await plantResponse.json();
            const categoryData = await categoryResponse.json();

            // On fusionne les résultats
            const searchResults = {
                plants: plantData,
                categories: categoryData
            };

            // Si l'une des requêtes fonctionne, on stock le resultat de la recherche dans le localStorage
            // et on redirige vers la page de résultat de recherche (ShopPage.jsx)
            if (plantData.length > 0 || categoryData.length > 0) {
                localStorage.setItem('searchResults', JSON.stringify(searchResults));
                navigate('/shop');
            } else {
                // Sinon, on affiche un message d'erreur
                setError("Aucun résultat trouvé.");
            }

        } catch (err) {
            setError(err.message);
            //console.error('Erreur de recherche:', err);
        } finally {
            setIsLoading(false);
        }
    }

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
                {/* Bouton de recherche stylisé */}
                <button type="submit" disabled={isLoading} className="absolute inset-y-0 right-0 px-4 flex items-center
                 bg-green-700 text-white rounded-r-md hover:bg-green-600 disabled:bg-green-400">
                    {isLoading ? '...' : <Search size={20}/>}
                </button>
            </form>
            {error && <p className="text-red-600 text-sm text-center mt-2">{error}</p>}
        </div>
    );
}

export default SearchBar;