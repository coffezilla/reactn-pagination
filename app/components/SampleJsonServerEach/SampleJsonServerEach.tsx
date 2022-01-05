import { useState } from 'react';
import PaginationJson from '../PaginationJson';

interface IData {
	data: string[];
}

const SampleJsonServerEach = () => {
	const [paginationCurrentData, setPaginationCurrentData] = useState<IData['data']>([]);
	return (
		<>
			<h1>Sample Json Server Each</h1>
			<h2>Result:</h2>
			<ul className="list-box-local">
				{paginationCurrentData.map((result: any) => {
					return <li key={result}>{result}</li>;
				})}
			</ul>
			<PaginationJson
				data="http://backend/users"
				setData={setPaginationCurrentData}
				saveLocalJson={false}
				pathData="list"
				perPage={20}
			/>
		</>
	);
};

export default SampleJsonServerEach;
