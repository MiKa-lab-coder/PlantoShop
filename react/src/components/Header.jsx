import { useState } from 'react';
import { Link } from 'react-router-dom';
import { ShoppingCart, User, Menu, X, LogOut, LayoutDashboard } from 'lucide-react';

// Le composant reçoit maintenant isLoggedIn, onLogout et isAdmin comme props
function Header({ isLoggedIn, onLogout, isAdmin }) {
  // État initial du menu burger
  const [isMenuOpen, setIsMenuOpen] = useState(false);

  return (
    <header className="bg-white shadow-md py-4 px-8 sticky top-0 z-50">
      <div className="container mx-auto flex justify-between items-center">
        {/* Titre du site */}
        <Link to="/" className="text-2xl font-bold text-green-700 hover:text-green-600">
            Plantoshop
        </Link>

        {/* Liens de navigation pour grand écran */}
        <nav className="hidden md:flex gap-6 items-center">
          <Link to="/" className="text-slate-600 hover:text-green-700">Accueil</Link>
          <Link to="/shop" className="text-slate-600 hover:text-green-700">Boutique</Link>
          <Link to="/contact" className="text-slate-600 hover:text-green-700">Nous Contacter</Link>
        </nav>

        {/* Icônes d'action */}
        <div className="hidden md:flex items-center gap-4">
          {/* Lien vers l'espace Admin si l'utilisateur est admin */}
          {isAdmin && (
            <Link to="/admin" className="text-slate-600 hover:text-green-700" title="Administration">
              <LayoutDashboard />
            </Link>
          )}

          {/* Lien vers l'espace utilisateur si connecté, ou vers la page de connexion si non connecté */}
          {isLoggedIn ? (
            <Link to="/user" className="text-slate-600 hover:text-green-700" title="Mon Compte">
              <User />
            </Link>
          ) : (
            <Link to="/login" className="text-slate-600 hover:text-green-700" title="Se connecter">
              <User />
            </Link>
          )}

          <Link to="/cart" className="text-slate-600 hover:text-green-700" title="Mon Panier">
            <ShoppingCart />
          </Link>

          {/* Bouton de déconnexion si l'utilisateur est connecté */}
          {isLoggedIn && (
            <button onClick={onLogout} className="text-red-600 hover:text-red-700" title="Déconnexion">
              <LogOut />
            </button>
          )}
        </div>

        {/* Icône du menu burger */}
        <div className="md:hidden">
          <button onClick={() => setIsMenuOpen(true)} className="text-slate-600">
            <Menu size={28} />
          </button>
        </div>
      </div>

      {/* Set de liens pour le menu mobile */}
      {isMenuOpen && (
        <div className="fixed inset-0 bg-white z-50 flex flex-col p-8">
          {/* Bouton pour fermer le menu */}
          <div className="flex justify-end mb-8">
            <button onClick={() => setIsMenuOpen(false)} className="text-slate-600">
              <X size={32} />
            </button>
          </div>

          {/* Liens de navigation pour le menu mobile */}
          <nav className="flex flex-col items-center justify-center flex-grow gap-8">
            <Link to="/" onClick={() => setIsMenuOpen(false)} className="text-2xl text-slate-800
             hover:text-green-600">Accueil</Link>
            <Link to="/shop" onClick={() => setIsMenuOpen(false)} className="text-2xl text-slate-800
             hover:text-green-600">Boutique</Link>
            <Link to="/contact" onClick={() => setIsMenuOpen(false)} className="text-2xl text-slate-800
             hover:text-green-600">Nous Contacter</Link>
            
            {/* Liens conditionnels pour le menu mobile */}
            {isAdmin && (
              <Link to="/admin" onClick={() => setIsMenuOpen(false)} className="text-2xl text-slate-800
               hover:text-green-600">Admin</Link>
            )}
            {isLoggedIn ? (
              <Link to="/user" onClick={() => setIsMenuOpen(false)} className="text-2xl text-slate-800
               hover:text-green-600">Mon Compte</Link>
            ) : (
              <Link to="/login" onClick={() => setIsMenuOpen(false)} className="text-2xl text-slate-800
               hover:text-green-600">Se connecter</Link>
            )}
            <Link to="/cart" onClick={() => setIsMenuOpen(false)} className="text-2xl text-slate-800
             hover:text-green-600">Panier</Link>
            {isLoggedIn && (
              <button onClick={() => { onLogout(); setIsMenuOpen(false); }} className="text-2xl
               text-red-600 hover:text-red-700">Déconnexion</button>
            )}
          </nav>
        </div>
      )}
    </header>
  );
}

export default Header;