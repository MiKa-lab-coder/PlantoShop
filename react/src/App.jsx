import { Outlet } from 'react-router-dom';
import { useState, useEffect } from 'react';
import Header from './components/Header';
import Footer from './components/Footer';
import './App.css';

function App() {
  const [isLoggedIn, setIsLoggedIn] = useState(false);

  // Au chargement de l'app, on vérifie si un token existe
  useEffect(() => {
    const token = localStorage.getItem('token');
    if (token) {
      setIsLoggedIn(true);
    }
  }, []);

  // Fonction pour gérer la connexion
  const handleLogin = () => {
    setIsLoggedIn(true);
  };

  // Fonction pour gérer la déconnexion
  const handleLogout = () => {
    localStorage.removeItem('token');
    setIsLoggedIn(false);
    // Rediriger vers la page d'accueil après la déconnexion
    window.location.href = '/';
  };

  return (
    <div className="flex flex-col min-h-screen bg-zinc-100">
      {/* On passe l'état de connexion et la fonction de déconnexion au Header */}
      <Header isLoggedIn={isLoggedIn} onLogout={handleLogout} />

      {/* Le contenu principal de la page */}
      <main className="flex-grow container mx-auto">
        {/* On passe la fonction de connexion aux composants enfants (LoginPage, etc.) */}
        <Outlet context={{ handleLogin }} />
      </main>

      <Footer />
    </div>
  );
}

export default App;