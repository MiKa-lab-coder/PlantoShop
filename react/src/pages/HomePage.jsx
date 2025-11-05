import SearchBar from '../components/SearchBar.jsx';
import wallpaper from '../assets/img/wallpaper.png'; // Importer l'image

function HomePage() {
  const pageStyle = {
    backgroundImage: `url(${wallpaper})`,
    backgroundSize: 'cover',
    backgroundPosition: 'center',
  };

  return (
    <div style={pageStyle} className="min-h-[calc(100vh-80px)] w-full flex flex-col items-center justify-center p-4">
      <div className="bg-white bg-opacity-90 p-8 rounded-lg shadow-lg text-center">
        <h2 className="text-3xl font-bold text-green-700 mb-4">Bienvenue sur PlantoShop !</h2>
        <p className="text-slate-600 mb-8">Trouvez la plante parfaite pour votre maison.</p>
        <SearchBar />
      </div>
    </div>
  );
}

export default HomePage;
