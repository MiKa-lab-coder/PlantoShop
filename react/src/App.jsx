import { Link, Outlet } from 'react-router-dom';
import './App.css';

function App() {
  return (
    <>
      <nav>
        <h1 className="text-2xl text-color-green-600 font-bold mb-4 text-center">PlantoShop</h1>
        <ul className="flex gap-4">
          <li>
            <Link to="/">Accueil</Link>
          </li>
          <li>
            <Link to="/login">Connexion</Link>
          </li>
        </ul>
      </nav>

      <hr className="my-4" />

      <main>
        {/* C'est ici que React Router affichera le composant de la page active */}
        <Outlet />
      </main>
    </>
  );
}

export default App;
