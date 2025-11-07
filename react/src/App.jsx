import {Outlet, useNavigate} from 'react-router-dom';
import {useState, useEffect} from 'react';
import Header from './components/Header';
import Footer from './components/Footer';
import './App.css';

function App() {
    const [isLoggedIn, setIsLoggedIn] = useState(false);
    const [userRoles, setUserRoles] = useState([]);
    const [isAdmin, setIsAdmin] = useState(false);
    const navigate = useNavigate();

    // Fonction pour décoder le JWT et extraire les informations
    const decodeJwt = (token) => {
        try {
            const base64Url = token.split('.')[1];
            const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
            const decodedPayload = JSON.parse(atob(base64));
            return decodedPayload;
        } catch (error) {
            console.error("Erreur lors du décodage du token :", error);
            return null;
        }
    };

    // Au chargement de l'app, on vérifie si un token existe et on décode les rôles
    useEffect(() => {
        const token = localStorage.getItem('token');
        if (token) {
            const decoded = decodeJwt(token);
            if (decoded) {
                setIsLoggedIn(true);
                const roles = decoded.roles || [];
                setUserRoles(roles);
                setIsAdmin(roles.includes('ROLE_ADMIN'));
            } else {
                // Si le token est invalide, on le supprime
                localStorage.removeItem('token');
                setIsLoggedIn(false);
                setUserRoles([]);
                setIsAdmin(false);
            }
        } else {
            setIsLoggedIn(false);
            setUserRoles([]);
            setIsAdmin(false);
        }
    }, []);

    // Fonction pour gérer la connexion
    const handleLogin = () => {
        const token = localStorage.getItem('token');
        if (token) {
            const decoded = decodeJwt(token);
            if (decoded) {
                setIsLoggedIn(true);
                const roles = decoded.roles || [];
                setUserRoles(roles);
                setIsAdmin(roles.includes('ROLE_ADMIN'));
            }
        }
    };

    // Fonction pour gérer la déconnexion
    const handleLogout = () => {
        localStorage.removeItem('token');
        setIsLoggedIn(false);
        setUserRoles([]);
        setIsAdmin(false);
        // Rediriger vers la page d'accueil en utilisant useNavigate
        navigate('/');
    };

    return (
        <div className="flex flex-col min-h-screen bg-zinc-100">
            {/* On passe l'état de connexion et la fonction de déconnexion au Header */}
            <Header isLoggedIn={isLoggedIn} onLogout={handleLogout} isAdmin={isAdmin}/>

            {/* Le contenu principal de la page */}
            <main className="flex-grow container mx-auto">
                {/* On passe les informations d'authentification aux composants enfants via le contexte */}
                <Outlet context={{handleLogin, handleLogout, isLoggedIn, userRoles, isAdmin}}/>
            </main>

            <Footer/>
        </div>
    );
}

export default App;