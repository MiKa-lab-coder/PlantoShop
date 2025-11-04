import { useState, useEffect } from 'react';
import './App.css';

function App() {
    // Etat initial
    const [plants, setPlants] = useState([]);
    const [error, setError] = useState(null);

    // useEffect execute le code au chargement de la page.
    useEffect(() => {
        // On fait l'appel à notre API Symfony !
        fetch('http://localhost/api/plants')
            .then(response => {
                if (!response.ok) {
                    throw new Error('La réponse du réseau n\'était pas ok');
                }
                return response.json();
            })
            .then(data => {
                // On met à jour notre état "plants" avec les données reçues.
                // React va automatiquement ré-afficher le composant.
                setPlants(data);
            })
            .catch(error => {
                // En cas d'erreur (CORS, API éteinte...), on la stocke.
                console.error('Il y a eu un problème avec l\'opération fetch:', error);
                setError(error.message);
            });
    }, []); // Le tableau vide signifie qu'il n'y a pas de dépendance.

    return (
        <>
            <h1>PlantoShop</h1>
            <h2>Liste des Plantes depuis l'API Symfony</h2>

            {/* Affiche un message d'erreur s'il y en a une */}
            {error && <div className="error">Erreur: {error}</div>}

            {/* Affiche la liste des plantes */}
            <ul>
                {plants.map(plant => (
                    <li key={plant.id}>
                        {plant.name} - {plant.price / 100} €
                    </li>
                ))}
            </ul>
        </>
    );
}

export default App;
