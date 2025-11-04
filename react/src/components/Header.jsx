import { useState } from 'react';
import { Link } from 'react-router-dom';
import { ShoppingCart, User, Menu, X } from 'lucide-react';

function Header() {
  // Etat initial du menu burger
  const [isMenuOpen, setIsMenuOpen] = useState(false);

  return (
    <header className="bg-white shadow-md py-4 px-8 sticky top-0 z-50">
      <div className="container mx-auto flex justify-between items-center">
        {/* Logo / Titre du site */}
        <Link to="/" className="text-2xl font-bold text-green-700">
          PlantoShop
        </Link>

        {/* Liens de navigation pour grand écran */}
        <nav className="hidden md:flex gap-6 items-center">
          <Link to="/" className="text-slate-600 hover:text-green-600">Accueil</Link>
          <Link to="/plants" className="text-slate-600 hover:text-green-600">Nos Plantes</Link>
          <Link to="/contact" className="text-slate-600 hover:text-green-600">Nous Contacter</Link>
        </nav>

        {/* Icônes d'action */}
        <div className="hidden md:flex items-center gap-4">
          <Link to="/login" className="text-slate-600 hover:text-green-600">
            <User />
          </Link>
          <Link to="/cart" className="text-slate-600 hover:text-green-600">
            <ShoppingCart />
          </Link>
        </div>

        {/* Icône du menu burger pour petit écran */}
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
            <Link to="/plants" onClick={() => setIsMenuOpen(false)} className="text-2xl text-slate-800
             hover:text-green-600">Nos Plantes</Link>
            <Link to="/about" onClick={() => setIsMenuOpen(false)} className="text-2xl text-slate-800
             hover:text-green-600">À Propos</Link>
            <Link to="/login" onClick={() => setIsMenuOpen(false)} className="text-2xl text-slate-800
             hover:text-green-600">Mon Compte</Link>
            <Link to="/cart" onClick={() => setIsMenuOpen(false)} className="text-2xl text-slate-800
             hover:text-green-600">Panier</Link>
          </nav>
        </div>
      )}
    </header>
  );
}

export default Header;
